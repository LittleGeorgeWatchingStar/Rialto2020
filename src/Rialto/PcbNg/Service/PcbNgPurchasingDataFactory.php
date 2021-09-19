<?php

namespace Rialto\PcbNg\Service;


use Doctrine\ORM\EntityManager;
use Gumstix\Storage\FileStorage;
use Rialto\Manufacturing\BuildFiles\PcbBuildFiles;
use Rialto\PcbNg\Api\BomQuote;
use Rialto\PcbNg\Api\Bundle;
use Rialto\PcbNg\Api\PcbPriceQuote;
use Rialto\PcbNg\Api\PnpData;
use Rialto\PcbNg\Api\UserBoard;
use Rialto\PcbNg\Exception\PcbNgClientException;
use Rialto\PcbNg\Exception\PcbNgPurchasingDataFactoryException;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Stock\Bin\BinStyle;
use Rialto\Stock\Bin\BinStyleRepo;
use Rialto\Stock\Item\ManufacturedStockItem;
use Rialto\Stock\Item\Version\ItemVersion;

class PcbNgPurchasingDataFactory
{
    const TURN_TIME_ECONO = 12; // Calendar days
    const TURN_TIME_STANDARD = 12; // Calendar days
    const TURN_TIME_EXPEDITE = 5; // Business days

    const INCREMENT_QUANTITY = 6;

    /** @var PcbNgClient */
    private $client;

    /** @var GerbersConverter */
    private $gerberConverter;

    /** @var LocationsConverter */
    private $locationsConverter;

    /** @var FileStorage */
    private $storage;

    /** @var BinStyleRepo */
    private $binStyleRepo;

    public function __construct(EntityManager $em,
                                PcbNgClient $client,
                                GerbersConverter $gerbersConverter,
                                LocationsConverter $locationsConverter,
                                FileStorage $storage)
    {
        $this->client = $client;
        $this->gerberConverter = $gerbersConverter;
        $this->locationsConverter = $locationsConverter;
        $this->storage = $storage;
        $this->binStyleRepo = $em->getRepository(BinStyle::class);
    }

    /**
     * @return PurchasingData[]
     * @throws PcbNgPurchasingDataFactoryException
     * @throws PcbNgClientException
     */
    public function createForBoard(ManufacturedStockItem $stockItem,
                                   ItemVersion $itemVersion): array
    {
        if (!$stockItem->isBoard()) {
            throw new PcbNgPurchasingDataFactoryException('Stock item is not a board.');
        }

        $bomItems = $itemVersion->getBomItems();
        foreach ($bomItems as $bomItem) {
            if ($bomItem->getComponent()->isPCB()) {
                // Create purchase data for BRD.
                $purchData = new PurchasingData($stockItem);
                $purchData->setVersion($itemVersion->getVersion());
                $purchData->setSupplier($this->client->getPcbNgSupplier());
                $purchData->setIncrementQty(self::INCREMENT_QUANTITY);
                $purchData->setBinStyle($this->binStyleRepo->findMatching(BinStyle::DEFAULT_STYLE));

                // Create purchase data for PCB.
                $pcbPurchData = new PurchasingData($bomItem->getStockItem());
                $pcbPurchData->setVersion($itemVersion->getVersion());
                $pcbPurchData->setSupplier($this->client->getPcbNgSupplier());
                $pcbPurchData->setIncrementQty(self::INCREMENT_QUANTITY);
                $pcbPurchData->setBinStyle($this->binStyleRepo->findMatching(BinStyle::DEFAULT_STYLE));

                // Create cost breaks.
                $buildFiles = PcbBuildFiles::create(
                    $bomItem->getComponent(),
                    $bomItem->getVersion(),
                    $this->storage);


                if (!$buildFiles->exists(PcbBuildFiles::GERBERS)) {
                    throw new PcbNgPurchasingDataFactoryException('Gerbers file not found.');
                }
                if (!$buildFiles->exists(PcbBuildFiles::BOARD_OUTLINE)) {
                    $boardOutlineData = null;
                } else  {
                    $boardOutlineData = $buildFiles->getContents(PcbBuildFiles::BOARD_OUTLINE);
                }
                if (!$buildFiles->exists(PcbBuildFiles::DRILL_EXCELLON_24)) {
                    $drillData = null;
                } else  {
                    $drillData = $buildFiles->getContents(PcbBuildFiles::DRILL_EXCELLON_24);
                }
                $gerbersZipData = $buildFiles->getContents(PcbBuildFiles::GERBERS);
                $gerbersZipData = $this->gerberConverter->convert($gerbersZipData, $boardOutlineData, $drillData);

                if (!$buildFiles->exists(PcbBuildFiles::XY)) {
                    throw new PcbNgPurchasingDataFactoryException('XY CSV not found.');
                }
                $locationsCsvData = $buildFiles->getContents(PcbBuildFiles::XY);
                $locationsCsvData = $this->locationsConverter->convert($locationsCsvData, $itemVersion);

                $auth = $this->client->getAuth();

                $bundle = $this->client->uploadGerbers($auth, "{$itemVersion->getFullSku()} - gerbers.zip", $gerbersZipData);
                $pcbPriceQuote = $this->client->getPcbPriceQuote($auth, $bundle);

                $pnpData = $this->client->uploadPnp($auth, $locationsCsvData);

                $batchesSvcQtySidesUnitPrices = $pcbPriceQuote->getBatchesSvcQtySidesUnitPrices();
                $lowestBomQuote = null; // Use lowest cost break to create user board.
                $lowestBomQuoteQuantity = PHP_INT_MAX; // Use lowest cost break to create user board.
                foreach (reset($batchesSvcQtySidesUnitPrices)[PcbPriceQuote::SERVICE_STANDARD] as $breakQty => $prices) {
                    $doubleSidedUnitPrice = $prices[PcbPriceQuote::ASSEMBLY_DOUBLE_SIDED];
                    $bomQuote = $this->client->getBomQuote($auth, $breakQty, $pnpData);

                    // Use lowest cost break to create user board.
                    if ($lowestBomQuoteQuantity > $breakQty) {
                        $lowestBomQuoteQuantity = $breakQty;
                        $lowestBomQuote = $bomQuote;
                    }

                    $splitSubtotal = 0.3*$doubleSidedUnitPrice;
                    $pcbAddSubtotal = 0.7*$doubleSidedUnitPrice;

                    $purchData->createCostBreak($breakQty, $splitSubtotal, self::TURN_TIME_STANDARD);
                    $pcbPurchData->createCostBreak($breakQty, $pcbAddSubtotal, self::TURN_TIME_STANDARD);
                }

                // Create user board.
                $userBoard = $this->createUserBoard(
                    $auth,
                    $itemVersion->getFullSku(),
                    $bundle,
                    $pnpData,
                    $lowestBomQuote);

                $purchData->setProductUrl($this->client->getStorefrontBoardUrl(
                    $userBoard->getId()
                ));
                $purchData->setQuotationNumber($userBoard->getId());
                $pcbPurchData->setQuotationNumber($userBoard->getId());

                return [$purchData, $pcbPurchData];
            }
        }

        throw new PcbNgPurchasingDataFactoryException("PCB not found.");
    }

    private function getBomQuoteTotal(BomQuote $bomQuote): float
    {
        $total = 0;
        foreach ($bomQuote->getItems() as $item) {
            $unitPriceUsd = floatval($item['part']['unit_price_usd'] ?? 0);
            $total += $unitPriceUsd;
        }
        return $total;
    }

    private function createUserBoard(string $auth,
                                     string $name,
                                     Bundle $bundle,
                                     PnpData $pnpData,
                                     ?BomQuote $bomQuote): UserBoard
    {
        $bom = null;
        if ($bomQuote) {
            $bom = $this->client->newBom(
                $auth,
                $bomQuote->getId(),
                $bundle->getId(),
                $pnpData->getId(),
                $pnpData->getParts());
        }

        $board = $this->client->newBoard(
            $auth,
            $bundle->getId(),
            $bom ? $bom->getId() : null);

        return $this->client->newUserBoard(
            $auth,
            $name,
            $board->getId());
    }
}
