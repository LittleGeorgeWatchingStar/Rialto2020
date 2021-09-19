<?php

namespace Rialto\Geppetto\Design;

use Doctrine\ORM\EntityManagerInterface;
use Rialto\Accounting\Currency\Currency;
use Rialto\Database\Orm\DbManager;
use Rialto\Exception\InvalidDataException;
use Rialto\Manufacturing\Bom\BomEvent;
use Rialto\Manufacturing\ManufacturingEvents;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Catalog\Template\PurchasingDataTemplate;
use Rialto\Sales\Price\Orm\ProductPriceRepository;
use Rialto\Sales\Price\ProductPrice;
use Rialto\Sales\Type\SalesType;
use Rialto\Shipping\Export\HarmonizationCode;
use Rialto\Stock\Cost\StandardCost;
use Rialto\Stock\Cost\StandardCostUpdater;
use Rialto\Stock\Item\Eccn;
use Rialto\Stock\Item\ManufacturedStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\StockItemFactory;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Publication\UrlPublication;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use UnexpectedValueException;

/**
 * Create the needed stock records in Rialto for a given Geppetto design
 * revisions.
 */
class DesignStockItemFactory
{
    const HARMONIZATION_CODE_ID = '8473300002';

    const ECCN_CODE = Eccn::COMPUTERS;

    const COUNTRY_OF_ORIGIN = 'US';

    /** @var EntityManagerInterface */
    private $em;

    /** @var DbManager */
    private $dbm;

    /** @var DesignStockItemTemplateFactory */
    private $templateFactory;

    /** @var StockItemFactory */
    private $stockItemFactory;

    /** @var StandardCostUpdater */
    private $stdCostUpdater;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var HarmonizationCode */
    private $harmonizationCode;


    public function __construct(EntityManagerInterface $em,
                                DbManager $dbm,
                                DesignStockItemTemplateFactory $templateFactory,
                                StockItemFactory $stockItemFactory,
                                StandardCostUpdater $updater,
                                EventDispatcherInterface $dispatcher)
    {
        $this->em = $em;
        $this->dbm = $dbm;
        $this->templateFactory = $templateFactory;
        $this->stockItemFactory = $stockItemFactory;
        $this->stdCostUpdater = $updater;
        $this->dispatcher = $dispatcher;

        $this->harmonizationCode = $this->em->find(
            HarmonizationCode::class,
            self::HARMONIZATION_CODE_ID);
        assertion($this->harmonizationCode !== null);
    }

    public function createStockRecords(DesignRevision2 $designRevision): DesignCreateResult
    {
        if (!$designRevision->getBoard()) {
            $result = $this->createStockItems($designRevision);
            $designRevision->setBoard($result->getBoard());
            return $result;
        }
        return $this->createItemVersions($designRevision);
    }

    private function createStockItems(DesignRevision2 $designRevision): DesignCreateResult
    {
        $pcb = $this->createPcb($designRevision);
        $board = $this->createBoard($designRevision, $pcb);

        $this->createVersionBom($board, $pcb, $designRevision);
        /* Stock items have to be flushed before standard cost records can
         * be created. */
        $this->em->flush();
        $this->createPurchasingData($pcb, $designRevision->getVersionCode());
        $this->createPurchasingData($board, $designRevision->getVersionCode());
        $this->setPrice($board, $designRevision->getPrice()); // If price is null, price will be set to 0.
        return new DesignCreateResult($board, [$pcb]);
    }

    private function createItemVersions(DesignRevision2 $designRevision): DesignCreateResult
    {
        $this->em->persist($this->createPcbVersion($designRevision));
        $this->em->persist($this->createBoardVersion($designRevision));
        $board = $designRevision->getBoard();
        $pcb = $designRevision->getPcb();

        $this->createVersionBom($board, $pcb, $designRevision);
        /* Stock items have to be flushed before standard cost records can
         * be created. */
        $this->em->flush();
        $this->createPurchasingData($pcb, $designRevision->getVersionCode());
        $this->createPurchasingData($board, $designRevision->getVersionCode());
        if ($designRevision->getPrice() !== null) {
            $this->setPrice($board, $designRevision->getPrice());
        }
        return new DesignCreateResult($board, [$pcb]);
    }

    private function createPcb(DesignRevision2 $designRevision): StockItem
    {
        $template = $this->templateFactory->createPcbTemplate($designRevision);
        $pcb = $this->stockItemFactory->create($template);
        $this->em->persist($pcb);
        $this->addPermalink($pcb, $designRevision->getDesignPermalink());
        return $pcb;
    }

    private function createBoard(DesignRevision2 $designRevision,
                                  StockItem $pcb): StockItem
    {
        $template = $this->templateFactory->createBoardTemplate($designRevision);
        $brdCode = $this->determineCode('BRD', $pcb);
        $template->setStockCode($brdCode);
        $board = $this->stockItemFactory->create($template);
        $board->setHarmonizationCode($this->harmonizationCode);
        $board->setEccnCode(self::ECCN_CODE);
        $board->setCountryOfOrigin(self::COUNTRY_OF_ORIGIN);
        $this->em->persist($board);
        $this->addPermalink($board, $designRevision->getDesignPermalink());
        return $board;
    }

    private function createPcbVersion(DesignRevision2 $designRevision): ItemVersion
    {
        $template = $this->templateFactory->createPcbVersionTemplate($designRevision);
        $template->setStockItem($designRevision->getPcb());
        return $template->create();
    }

    private function createBoardVersion(DesignRevision2 $designRevision): ItemVersion
    {
        $template = $this->templateFactory->createBoardVersionTemplate($designRevision);
        $template->setStockItem($designRevision->getBoard());
        return $template->create();
    }

    private function createVersionBom(StockItem $board,
                                      StockItem $pcb,
                                      DesignRevision2 $designRevision): void
    {
        $version = $board->getVersion($designRevision->getVersionCode());
        $bomItem = $version->addComponent($pcb, 1);
        $bomItem->setVersion($pcb->getVersion($designRevision->getVersionCode()));
        $bomItem->setWorkType(WorkType::fetchSmt($this->em));

        foreach( $designRevision->getModules() as $module ) {
            $module->addComponentsToBom($version);
        }

        $version->resetWeightFromBom();

        $event = new BomEvent($version);
        $this->dispatcher->dispatch(ManufacturingEvents::NEW_BOM, $event);
    }

    private function createPurchasingData(StockItem $item,
                                          string $versionCode): void
    {
        $version = $item->getVersion($versionCode);
        $templates = $this->em->getRepository(PurchasingDataTemplate::class)
            ->findAll();
        /** @var PurchasingData|null $lowest */
        $lowest = null;
        foreach ( $templates as $template ) {
            /* @var $template PurchasingDataTemplate */
            if ($template->appliesTo($item)) {
                if ($template->alreadyExists($version, $this->em)) {
                    throw new UnexpectedValueException(
                        "Purchasing data already exists for $item-R$version");
                }
                $purchData = $template->createFor($version);
                $this->em->persist($purchData);
                $templateCost = $purchData->getMinimumOrderExtendedCost();
                $lowestCost = $lowest ? ($lowest->getMinimumOrderExtendedCost()) : PHP_INT_MAX;
                if (($templateCost !== null) && ($templateCost < $lowestCost)) {
                    $lowest = $purchData;
                    $item->setEconomicOrderQty($purchData->getIncrementQty());
                    $this->updateStandardCost($item, $version, $purchData);
                }
            }
        }
        if (!$lowest) {
            throw new InvalidDataException("No purchasing data template exists for $item-R$version");
        }
        if ($lowest->getId() === null) {
            $lowest->setPreferred();
        }
    }

    private function updateStandardCost(StockItem $item,
                                        Version $version,
                                        PurchasingData $purchData): void
    {
        $stdCost = new StandardCost($item);
        if ( $item->isManufactured() ) {
            /* @var $item ManufacturedStockItem */
            $material = $item->getBom($version)->getTotalStandardCost();
            $labour = $purchData->getCost();
        } else {
            $material = $purchData->getCost();
            $labour = 0;
        }
        $stdCost->setMaterialCost($material);
        $stdCost->setLabourCost($labour);
        $this->stdCostUpdater->update($stdCost);
    }

    private function setPrice(StockItem $board, ?float $amount)
    {
        $currency = Currency::findUSD($this->dbm);
        $type = SalesType::fetchOnlineSale($this->dbm);
        $repo = $this->em->getRepository(ProductPrice::class);
        /* @var $repo ProductPriceRepository */
        $price = $repo->findOrCreate($board, $type, $currency);
        $price->setPrice($amount);
        $this->em->persist($price);
    }


    private function addPermalink(StockItem $item, $permalink)
    {
        if (! $permalink) {
            return;
        }
        $pub = new UrlPublication($item);
        $pub->setDescription('Geppetto permalink');
        $pub->setUrl($permalink);
        $this->em->persist($pub);
    }

    private function determineCode(string $prefix, StockItem $stockItem): string
    {
        assert(preg_match('/\A[A-Z]{3}\z/', $prefix));
        return preg_replace('/\A[A-Z]{3}/', $prefix, $stockItem->getSku());
    }
}
