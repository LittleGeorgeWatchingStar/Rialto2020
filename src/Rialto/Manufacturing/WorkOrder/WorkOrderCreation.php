<?php

namespace Rialto\Manufacturing\WorkOrder;

use Rialto\Allocation\Validator\PurchasingDataExistsForChild;
use Rialto\Database\Orm\DbManager;
use Rialto\IllegalStateException;
use Rialto\Manufacturing\Allocation\CanCreateChild;
use Rialto\Manufacturing\Bom\BomException;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\Customization\Validator\CustomizationMatchesVersion;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Order\PurchaseInitiator;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\ManufacturedStockItem;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Item\Version\VersionIsSpecified;
use Rialto\Stock\VersionedItem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Controls the creation of a work order and many associated records:
 * the requirements, a child work order, the purchase order, etc.
 *
 * @PurchasingDataExistsForChild
 * @CustomizationMatchesVersion
 */
class WorkOrderCreation implements VersionedItem, PurchaseInitiator, CanCreateChild
{
    const INITIATOR_CODE = 'WOSystem';

    /** @var StockItem */
    private $parentItem;

    /**
     * @var Version
     * @Assert\NotNull
     * @VersionIsSpecified
     */
    private $parentVersion;

    /**
     * @var Customization
     */
    private $customization = null;

    /**
     * @var PurchasingData|null
     * @Assert\NotNull(message="Build location is required.")
     */
    private $purchData = null;

    /**
     * @var int
     * @Assert\Type(type="integer")
     * @Assert\Range(min=1)
     */
    private $qtyOrdered;

    private $openForAllocation = false;

    private $instructions = '';

    /** @var ManufacturedStockItem */
    private $childItem;

    private $createChild = false;

    public function __construct(
        ManufacturedStockItem $itemToBuild,
        Version $version = null)
    {
        $this->parentItem = $itemToBuild;
        $this->parentVersion = $itemToBuild->getSpecifiedVersionOrDefault($version);
        $this->qtyOrdered = max($itemToBuild->getEconomicOrderQty(), 1);
    }

    public function getInitiatorCode()
    {
        return self::INITIATOR_CODE;
    }

    public function loadDefaultValues(DbManager $dbm)
    {
        $this->purchData = $this->loadPurchasingData($dbm);
        if (! $this->purchData) {
            return;
        }

        $this->childItem = $this->findChildItem($dbm);
        if ($this->childItem) {
            $this->createChild = $this->childPurchasingDataExists($dbm);
        }
    }

    /** @return PurchasingData|null */
    private function loadPurchasingData(DbManager $dbm)
    {
        /** @var $repo PurchasingDataRepository */
        $repo = $dbm->getRepository(PurchasingData::class);
        /** @var PurchasingData|null $purchData */
        $purchData = $repo->createBuilder()
            ->isActive()
            ->byItem($this->parentItem, $this->qtyOrdered)
            ->byVersion($this->parentVersion)
            ->orderByPreferred()
            ->getFirstResultOrNull();
        return $purchData;
    }

    public function getPurchasingData()
    {
        return $this->purchData;
    }

    public function setPurchasingData(PurchasingData $purchData)
    {
        $this->purchData = $purchData;
    }

    private function findChildItem(DbManager $dbm)
    {
        if (! $this->parentVersion) {
            return null;
        }
        /** @var $repo StockItemRepository */
        $repo = $dbm->getRepository(StockItem::class);
        return $repo->findComponentBoard(
            $this->parentItem,
            $this->parentVersion
        );
    }


    /** @return PurchasingData|object|null */
    private function getChildPurchasingData(DbManager $dbm)
    {
        /** @var $repo PurchasingDataRepository */
        $repo = $dbm->getRepository(PurchasingData::class);
        return $repo->createBuilder()
            ->isActive()
            ->byItem($this->childItem, $this->qtyOrdered)
            ->byVersion($this->getChildVersion())
            ->byLocation($this->getLocation())
            ->orderByPreferred()
            ->getFirstResultOrNull();
    }

    private function childPurchasingDataExists(DbManager $dbm)
    {
        return (bool) $this->getChildPurchasingData($dbm);
    }


    /** @return Version */
    public function getChildVersion()
    {
        $bom = $this->parentItem->getBom($this->parentVersion);
        $childBomItem = $bom->getItem($this->childItem);
        if (! $childBomItem) {
            throw new BomException($bom, sprintf('No such item %s in %s',
                $this->childItem,
                $bom));
        }
        $version = $childBomItem->getVersion();
        if ($version->isSpecified()) {
            return $version;
        }
        return $this->childItem->getAutoBuildVersion();
    }

    /** @return Facility */
    public function getLocation()
    {
        return $this->purchData ? $this->purchData->getBuildLocation() : null;
    }

    public function getBuildLocation()
    {
        return $this->getLocation();
    }

    /** @return Supplier */
    public function getSupplier()
    {
        return $this->purchData->getSupplier();
    }

    /** @return ManufacturedStockItem */
    public function getParentItem()
    {
        return $this->parentItem;
    }

    public function getStockItem()
    {
        return $this->getParentItem();
    }

    public function getSku()
    {
        return $this->parentItem->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function getVersion()
    {
        return $this->parentVersion;
    }

    public function setVersion(Version $parentVersion)
    {
        assertion($parentVersion->isSpecified());
        $this->parentVersion = $parentVersion;
    }

    public function getCustomization()
    {
        return $this->customization;
    }

    public function setCustomization(Customization $customization = null)
    {
        $this->customization = $customization;
    }

    public function getFullSku()
    {
        return $this->getSku()
        . $this->parentVersion->getStockCodeSuffix()
        . Customization::getStockCodeSuffix($this->customization);
    }

    /** @deprecated */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    public function isIssued()
    {
        return false;
    }

    public function getInstructions()
    {
        return $this->instructions;
    }

    public function setInstructions($instructions)
    {
        $this->instructions = trim($instructions);
    }

    /** @return StockItem|null */
    public function getChildItem()
    {
        return $this->childItem;
    }

    public function getQtyOrdered()
    {
        return $this->qtyOrdered;
    }

    public function setQtyOrdered($qty)
    {
        $this->qtyOrdered = $qty;
    }

    public function getOpenForAllocation()
    {
        return $this->openForAllocation;
    }

    public function setOpenForAllocation($openForAllocation)
    {
        $this->openForAllocation = $openForAllocation;
    }

    public function hasChild()
    {
        return $this->childItem != null;
    }

    public function isCreateChild()
    {
        return $this->createChild;
    }

    public function setCreateChild($bool)
    {
        $this->createChild = $bool;
    }

    /** @return WorkOrder */
    public function createParentOrder(PurchaseOrder $po)
    {
        /** @var $parent WorkOrder */
        $parent = $po->addItemFromPurchasingData($this->purchData, $this->parentVersion);
        assertion($parent instanceof WorkOrder);
        $parent->setQtyOrdered($this->qtyOrdered);
        $parent->setCustomization($this->customization);
        $parent->setInstructions($this->instructions);
        $parent->setOpenForAllocation($this->openForAllocation);
        return $parent;
    }

    /** @return WorkOrder */
    public function createChildOrder(PurchaseOrder $po, DbManager $dbm)
    {
        if (! $this->createChild) {
            throw new IllegalStateException();
        }
        $purchData = $this->getChildPurchasingData($dbm);
        /** @var $child WorkOrder */
        $child = $po->addItemFromPurchasingData($purchData, $this->getChildVersion());
        assertion($child instanceof WorkOrder);
        $child->setQtyOrdered($this->qtyOrdered);
        return $child;
    }

    /** @Assert\Callback */
    public function validateParentBom(ExecutionContextInterface $context)
    {
        if (! $this->parentItem->bomExists($this->parentVersion)) {
            $context->buildViolation("No BOM exists for this version.")
                ->atPath('version')
                ->addViolation();
        }
    }

    /** @Assert\Callback */
    public function validateChildOrder(ExecutionContextInterface $context)
    {
        if (! $this->createChild) {
            return;
        }
        if (! $this->childItem) {
            $context->addViolation('There is no child item for {parent}.', [
                '{parent}' => $this->getFullSku(),
            ]);
            return;
        }
        $this->validateChildBom($context);
    }

    private function validateChildBom(ExecutionContextInterface $context)
    {
        $childVersion = $this->getChildVersion();
        if (! $this->childItem->bomExists($childVersion)) {
            $context->addViolation("No BOM exists for {child}{version}.", [
                '{child}' => $this->childItem,
                '{version}' => $childVersion->getStockCodeSuffix()
            ]);
        }
    }
}
