<?php

namespace Rialto\Panelization;


use Gumstix\Filetype\CsvFile;
use Gumstix\Geometry\Rectangle;
use Gumstix\Geometry\Vector2D;
use Gumstix\Storage\FileStorage;
use Rialto\Exception\InvalidDataException;
use Rialto\IllegalStateException;
use Rialto\Manufacturing\BuildFiles\BuildFiles;
use Rialto\Manufacturing\BuildFiles\PcbBuildFiles;
use Rialto\Manufacturing\Component\Component;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Measurement\Dimensions;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\VersionedItem;

/**
 * A board that has been added at a particular position on a Panel.
 */
class PlacedBoard implements VersionedItem
{
    /** @var string */
    private $id;

    /** @var Panel|null */
    private $panel = null;

    /** @var WorkOrder */
    private $workOrder = null;

    /**
     * The position and orientation of this board on the panel.
     *
     * @var Pose
     */
    private $pose;

    /**
     * Uniquely identifies this board on the panel.
     *
     * Set when the board is positioned on the panel.
     *
     * @var int
     */
    private $panelIndex = null;

    private $margin = Panel::DEFAULT_MARGIN;

    public function __construct(WorkOrder $wo)
    {
        $this->workOrder = $wo;
        $this->pose = new Pose(0, 0, 0);
    }

    /** @return string */
    public function getId()
    {
        return $this->id;
    }

    /** @return Customization|null */
    public function getCustomization()
    {
        return $this->workOrder->getCustomization();
    }

    public function getSku()
    {
        return $this->workOrder->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /** @return Version */
    public function getVersion()
    {
        return $this->workOrder->getVersion();
    }

    /** @return StockItem */
    public function getStockItem()
    {
        return $this->workOrder->getStockItem();
    }

    /** @deprecated */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    public function getFullSku()
    {
        return $this->workOrder->getFullSku();
    }

    public function __toString()
    {
        return $this->getFullSku();
    }

    public function getPurchaseOrder(): PurchaseOrder
    {
        return $this->workOrder->getPurchaseOrder();
    }

    public function getPurchasingData(): PurchasingData
    {
        return $this->workOrder->getPurchasingData();
    }

    public function getPose(): Pose
    {
        return $this->pose;
    }

    public function setPose(Pose $position)
    {
        $this->pose = $position;
    }

    public function setPanel(Panel $panel, int $panelIndex)
    {
        $this->panel = $panel;
        $this->panelIndex = $panelIndex;
    }

    public function translate(Vector2D $vector)
    {
        $this->pose = $this->pose->translate($vector);
    }

    /**
     * The dimensions of the board's PCB in millimeters.
     */
    public function getDimensions(): Dimensions
    {
        $dimensions = $this->getPcbVersion()->getDimensions();
        if ($dimensions) {
            return $dimensions->inMm();
        }
        throw new IllegalStateException("PCB for {$this->workOrder} has no dimensions");
    }

    private function getPcbVersion(): ItemVersion
    {
        foreach ($this->workOrder->getRequirements() as $req) {
            if ($req->isCategory(StockCategory::PCB)) {
                $item = $req->getStockItem();
                $version = $req->getVersion();
                return $item->getVersion($version);
            }
        }
        throw new IllegalStateException("{$this->workOrder} has no PCB requirement");
    }

    /**
     * The rectangle around the board with which no other board should overlap.
     */
    public function getKeepout(): Rectangle
    {
        $box = $this->getBoundingBox()->normalize();
        $margin = $this->getMargin();
        $origin = $box->getOrigin()->subtract($margin);
        $opposite = $box->getOpposite()->add($margin);
        $keepout = Rectangle::fromCorners($origin, $opposite);
        assertion($keepout->contains($box));
        return $keepout;
    }

    public function getBoundingBox(): Rectangle
    {
        return $this->pose->createRectangle($this->getDimensions());
    }

    /**
     * @param float $margin
     */
    public function setMargin($margin)
    {
        $this->margin = $margin;
    }

    private function getMargin(): Vector2D
    {
        $width = $this->margin;
        return new Vector2D($width, $width);
    }

    /**
     * True if they overlap.
     */
    public function overlaps(PlacedBoard $other): bool
    {
        return $this->getKeepout()->overlaps($other->getBoundingBox());
    }

    public function setWorkOrder(WorkOrder $workOrder)
    {
        $this->workOrder = $workOrder;
    }

    public function getBoardId(): string
    {
        assertion(null !== $this->panelIndex);
        return sprintf('%s-%s',
            substr($this->getSku(), -5),
            $this->panelIndex);
    }

    public function addComponentsToConsolidatedBom(ConsolidatedBom $bom)
    {
        foreach ($this->workOrder->getAllComponents() as $component) {
            if (!$component->isCategory(StockCategory::PCB)) {
                $bom->add($component, $this->getBoardId());
            }
        }
    }

    public function addComponentsToConsolidatedXY(ConsolidatedXY $xy,
                                                  FileStorage $buildFilesStorage)
    {
        $pcb = $this->getPcbComponent();
        $xyData = $this->loadXyData($pcb, $buildFilesStorage);
        foreach ($xyData as $row) {
            $xy->addRow($row, $this->pose, $this->getBoardId());
        }
    }

    private function getPcbComponent(): Component
    {
        foreach ($this->workOrder->getAllComponents() as $component) {
            if ($component->isCategory(StockCategory::PCB)) {
                return $component;
            }
        }
        $msg = "{$this->workOrder} has no PCB component";
        throw new InvalidDataException($msg);
    }

    private function loadXyData(Component $pcb,
                                FileStorage $buildFilesStorage): CsvFile
    {
        $buildFiles = BuildFiles::create(
            $pcb->getStockItem(),
            $pcb->getVersion(),
            $buildFilesStorage);
        if (!$buildFiles->exists(PcbBuildFiles::XY)) {
            $sku = $pcb->getFullSku();
            throw new InvalidDataException("$sku has no XY data");
        }
        $xyData = $buildFiles->getContents(PcbBuildFiles::XY);
        assertion(null != $xyData);
        $xyFile = new CsvFile();
        $xyFile->parseString($xyData);
        return $xyFile;
    }
}
