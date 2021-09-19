<?php

namespace Rialto\Geppetto\Design;

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
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Publication\UrlPublication;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use UnexpectedValueException;

/**
 * @deprecated
 * use @see DesignStockItemFactory
 *
 * Creates the needed stock records in Rialto for a given Geppetto design.
 */
class DesignFactory
{
    const HARMONIZATION_CODE = '8473300002';

    /** @var DbManager */
    private $dbm;

    /** @var StockItemFactory */
    private $itemFactory;

    /** @var StandardCostUpdater */
    private $stdCostUpdater;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var HarmonizationCode */
    private $harmonizationCode;

    private $eccnCode = Eccn::COMPUTERS;

    private $countryOfOrigin = 'US';

    public function __construct(
        DbManager $dbm,
        StockItemFactory $itemFactory,
        StandardCostUpdater $updater,
        EventDispatcherInterface $dispatcher)
    {
        $this->dbm = $dbm;
        $this->itemFactory = $itemFactory;
        $this->stdCostUpdater = $updater;
        $this->dispatcher = $dispatcher;
        $this->harmonizationCode = $this->dbm->need(
            HarmonizationCode::class,
            self::HARMONIZATION_CODE);
    }

    /**
     * Creates board and PCB stock items for the given design.
     *
     * @return StockItem the board item
     */
    public function create(Design $design)
    {
        $pcb = $this->createPcb($design);
        $board = $this->createBoard($design, $pcb);
        $this->createBom($board, $pcb, $design);
        /* Stock items have to be flushed before standard cost records can
         * be created. */
        $this->dbm->flush();
        $this->createPurchasingData($pcb, $design->getVersionCode());
        $this->createPurchasingData($board, $design->getVersionCode());
        $this->setPrice($board, $design->price);
        return $board;
    }

    /** @return StockItem */
    private function createPcb(Design $design)
    {
        $template = $design->createPcbTemplate($this->dbm);
        $pcb = $this->itemFactory->create($template);
        $this->dbm->persist($pcb);
        $this->addPermalink($pcb, $design->permalink);
        $design->pcb = $pcb;
        return $pcb;
    }

    private function addPermalink(StockItem $item, $permalink)
    {
        if (! $permalink) {
            return;
        }
        $pub = new UrlPublication($item);
        $pub->setDescription('Geppetto permalink');
        $pub->setUrl($permalink);
        $this->dbm->persist($pub);
    }

    /** @return StockItem */
    private function createBoard(Design $design, StockItem $pcb)
    {
        $template = $design->createBoardTemplate($this->dbm);
        $brdCode = $this->determineCode('BRD', $pcb);
        $template->setStockCode($brdCode);
        $board = $this->itemFactory->create($template);
        $board->setHarmonizationCode($this->harmonizationCode);
        $board->setEccnCode($this->eccnCode);
        $board->setCountryOfOrigin($this->countryOfOrigin);
        $this->dbm->persist($board);
        $this->addPermalink($board, $design->permalink);
        $design->board = $board;
        return $board;
    }

    private function createBom(StockItem $board, StockItem $pcb, DesignAbstract $design)
    {
        $version = $board->getVersion($design->getVersionCode());
        $bomItem = $version->addComponent($pcb, 1);
        $bomItem->setVersion($pcb->getVersion($design->getVersionCode()));
        $bomItem->setWorkType(WorkType::fetchSmt($this->dbm));

        foreach ( $design->getModules() as $module ) {
            $module->addComponentsToBom($version);
        }

        $version->resetWeightFromBom();

        $event = new BomEvent($version);
        $this->dispatcher->dispatch(ManufacturingEvents::NEW_BOM, $event);
    }

    private function createPurchasingData(StockItem $item, $versionCode): void
    {
        $version = $item->getVersion($versionCode);
        $templates = $this->dbm->getRepository(PurchasingDataTemplate::class)
            ->findAll();
        /** @var PurchasingData|null $lowest */
        $lowest = null;
        foreach ($templates as $template) {
            /* @var $template PurchasingDataTemplate */
            if ($template->appliesTo($item)) {
                if ($template->alreadyExists($version, $this->dbm)) {
                    throw new UnexpectedValueException(
                        "Purchasing data already exists for $item-R$version");
                }
                $purchData = $template->createFor($version);
                $this->dbm->persist($purchData);
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

    private function updateStandardCost(StockItem $item, Version $version, PurchasingData $purchData)
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

    private function setPrice(StockItem $board, $amount)
    {
        $currency = Currency::findUSD($this->dbm);
        $type = SalesType::fetchOnlineSale($this->dbm);
        $repo = $this->dbm->getRepository(ProductPrice::class);
        /* @var $repo ProductPriceRepository */
        $price = $repo->findOrCreate($board, $type, $currency);
        $price->setPrice($amount);
        $this->dbm->persist($price);
    }

    public function createRevision(DesignRevision $revision)
    {
        $this->dbm->persist($revision->createBoardVersion());
        $this->dbm->persist($revision->createPcbVersion());
        $board = $revision->getBoard();
        $pcb = $revision->getPcb();
        $this->createBom($board, $pcb, $revision);
        /* Stock items have to be flushed before standard cost records can
         * be created. */
        $this->dbm->flush();
        $this->createPurchasingData($pcb, $revision->getVersionCode());
        $this->createPurchasingData($board, $revision->getVersionCode());
        if (null !== $revision->price) {
            $this->setPrice($board, $revision->price);
        }
    }

    /** @return StockItem */
    public function createCad(Design $design, StockItem $board)
    {
        $template = $design->createCadTemplate($this->dbm);
        $cadCode = $this->determineCode('CAD', $board);
        $template->setStockCode($cadCode);
        $cad = $this->itemFactory->create($template);
//        $cad->setHarmonizationCode($this->harmonizationCode); // TODO: Confirm this.
//        $cad->setEccnCode($this->eccnCode); // TODO: Confirm this.
        $cad->setCountryOfOrigin($this->countryOfOrigin);
        $this->addPermalink($cad, $design->permalink);
        $this->dbm->persist($cad);
        $design->cad = $cad;
        return $cad;
    }

    /** @return string */
    private function determineCode(string $prefix, StockItem $stockItem)
    {
        assert(preg_match('/\A[A-Z]{3}\z/', $prefix));
        return preg_replace('/\A[A-Z]{3}/', $prefix, $stockItem->getSku());
    }
}
