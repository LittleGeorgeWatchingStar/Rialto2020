<?php

namespace Rialto\Sales\Order\Allocation;

use Rialto\Allocation\Validator\PurchasingDataExistsForChild;
use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\Allocation\CanCreateChild;
use Rialto\Manufacturing\WorkOrder\WorkOrderCreation;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Producer\StockProducerFactory;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * Implements CanCreateChild so we can validate that child purchasing data
 * exists if needed.
 *
 * @PurchasingDataExistsForChild
 */
class SalesOrderDetailAllocatorManufactured
    extends SalesOrderDetailAllocator
    implements CanCreateChild
{
    /** @var PurchasingDataRepository */
    private $purchasingDataRepo;

    /**
     * We use an instance of WorkOrderCreation so we can implement
     * CanCreateChild.
     *
     * @var WorkOrderCreation
     */
    private $woCreation;

    /** @var int */
    private $qtyToOrder;

    /** @var int */
    private $fabQtyToOrder;

    /** @var PurchasingData|null */
    private $purchasingData;

    /** @var Facility|null */
    private $buildLocation;

    /**
     * Selection of build locations.
     * @var Facility[]
     */
    private $buildLocations = [];

    /**
     * PCB's suppliers array
     * @var Supplier[]
     */
    private $suppliers = [];

    /**
     * PCB's purchasingData
     * @var PurchasingData
     */
    private $pcbPurchasingData;

    /**
     * PCB's purchasingData array
     * @var PurchasingData[]
     */
    private $pcbPurchasingDataChoices = [];

    /** @var Supplier */
    private $preferredSupplier;

    /** @var PurchasingData */
    private $preferredpcbPurchasingData;

    protected function __construct(Requirement $requirement, DbManager $dbm)
    {
        parent::__construct($requirement, $dbm);
        $this->purchasingDataRepo = $dbm->getRepository(PurchasingData::class);

        $this->woCreation = new WorkOrderCreation(
            $requirement->getStockItem(),
            $requirement->getVersion());
        $this->woCreation->loadDefaultValues($dbm);

        $this->qtyToOrder = StockProducerFactory::getQtyToOrder($requirement);

        $this->fabQtyToOrder = StockProducerFactory::getFabQtyToOrder($requirement);

        $this->suppliers = $this->getFabVendors();

        $this->pcbPurchasingDataChoices = $this->getPcbPurchasingDataChoices();
    }

    public function getQtyToOrder(): int
    {
        return $this->qtyToOrder;
    }

    public function setQtyToOrder(int $qty)
    {
        $this->qtyToOrder = $qty;
    }

    public function getFabQtyToOrder(): int
    {
        return $this->fabQtyToOrder;
    }

    public function setFabQtyToOrder(int $qty)
    {
        $this->fabQtyToOrder = $qty;
    }

    public function isCreateChild()
    {
        return $this->woCreation->isCreateChild();
    }

    public function setCreateChild($bool)
    {
        $this->woCreation->setCreateChild($bool);
    }

    public function getParentItem()
    {
        return $this->woCreation->getParentItem();
    }

    public function getChildItem()
    {
        return $this->woCreation->getChildItem();
    }

    public function getChildVersion()
    {
        return $this->woCreation->getChildVersion();
    }

    /**
     * @return PurchasingData|null
     */
    private function getDefaultPurchasingData()
    {
        return $this->woCreation->getPurchasingData();
    }

    /**
     * @return PurchasingData|null
     */
    public function getPurchasingData()
    {
        return $this->buildLocation ? $this->purchasingData : $this->getDefaultPurchasingData();
    }

    public function getPurchasingDataId()
    {
        $purchasingData = $this->buildLocation ? $this->purchasingData : $this->getDefaultPurchasingData();
        return $purchasingData->getId();
    }

    /**
     * @return Facility|null
     */
    public function getDefaultBuildLocation()
    {
        return $this->woCreation->getBuildLocation();
    }

    public function getBuildLocation()
    {
        return $this->buildLocation ?: $this->getDefaultBuildLocation();
    }

    /**
     * @param Facility|null $buildLocation
     */
    public function setBuildLocation($buildLocation)
    {
        $this->buildLocation = $buildLocation;

        if ($buildLocation) {
            $purchasingData = $this->purchasingDataRepo->findPreferredByLocationAndVersion(
                $buildLocation,
                $this->woCreation->getParentItem(),
                $this->woCreation->getVersion(),
                $this->woCreation->getQtyOrdered());
            $this->purchasingData = $purchasingData;
        } else {
            $this->purchasingData = null;
        }
    }

    /**
     * @return Facility[]
     */
    public function getBuildLocations(): array
    {
        if ($this->getDefaultBuildLocation() && !in_array($this->getDefaultBuildLocation(), $this->buildLocations)) {
            array_unshift($this->buildLocations, $this->getDefaultBuildLocation());
        }
        return $this->buildLocations;
    }

    /**
     * @return Supplier|null
     */
    public function getDefaultSupplier()
    {
        return $this->preferredSupplier;
    }

    /**
     * @param Supplier
     */
    public function setDefaultSupplier(Supplier $supplier)
    {
        return $this->preferredSupplier = $supplier;
    }

    /**
     * @return PurchasingData|null
     */
    public function getDefaultpcbPurchasingData()
    {
        return $this->preferredpcbPurchasingData;
    }

    /**
     * @param PurchasingData
     */
    public function setDefaultpcbPurchasingData(PurchasingData $purchasingData)
    {
        return $this->preferredpcbPurchasingData = $purchasingData;
    }

    /**
     * @return Supplier[]
     */
    public function getSuppliers(): array
    {
        return $this->suppliers;
    }

    /**
     * @param Facility[] $buildLocations
     */
    public function setBuildLocations(array $buildLocations)
    {
        $this->buildLocations = $buildLocations;
    }

    /**
     * @return Supplier[]
     */
    public function getFabVendors(): array
    {
        $suppliers = [];
        $pcbItem = $this->getPCB();

        if ($pcbItem != null) {
            foreach ($pcbItem->getAllPurchasingData() as $purchasingData) {
                /** @var PurchasingData $purchasingData */
                array_push($suppliers, $purchasingData->getSupplier());
                if ($purchasingData->isPreferred()) {
                    $this->setDefaultSupplier($purchasingData->getSupplier());
                }
            }
        }
        return array_unique($suppliers);
    }

    /**
     * @param Supplier[] $suppliers
     */
    public function setFebVendors(array $suppliers)
    {
        $this->suppliers = $suppliers;
    }

    /**
     * @return PurchasingData[]
     */
    public function getPcbPurchasingDataChoices(): array
    {
        $resultPurchasingData = [];
        $pcbItem = $this->getPCB();
        /** @var Version $salesOrderVersion */
        $salesOrderVersion = $this->getVersion();
        /** @var Version $salesOrderAutoBuildVersion */
        $salesOrderAutoBuildVersion = $this->getAutoBuildVersion();

        if ($pcbItem != null) {
            $purchasingData = $pcbItem->getAllPurchasingData();

            foreach ($purchasingData as $singularPurchasingData) {
                /** @var PurchasingData $singularPurchasingData */
                if ($singularPurchasingData->isPreferred()) {
                    $this->setDefaultpcbPurchasingData($singularPurchasingData);
                } else {
                    if (!$salesOrderVersion->isAny()) {
                        if ($singularPurchasingData->getVersion() !== null) {
//                            if ($salesOrderVersion->equals($singularPurchasingData->getVersion())) {
                                $resultPurchasingData[] = $singularPurchasingData;
//                            }
                        }
                    } else if ($salesOrderVersion->isAny()){
                        if ($singularPurchasingData->getVersion() !== null) {
//                            if ($salesOrderAutoBuildVersion->equals($singularPurchasingData->getVersion())) {
                                $resultPurchasingData[] = $singularPurchasingData;
//                            }
                        }
                    }
                }
            }

            if (!$salesOrderVersion->isAny()) {
                if ($this->getDefaultpcbPurchasingData() !== null) {
                    if ($salesOrderVersion->equals($this->getDefaultpcbPurchasingData()->getVersion())) {
                        if ($this->getDefaultpcbPurchasingData() && !in_array($this->getDefaultpcbPurchasingData(), $resultPurchasingData)) {
                            array_unshift($resultPurchasingData, $this->getDefaultpcbPurchasingData());
                        }
                    }
                }
            } else if ($salesOrderVersion->isAny()){
                if ($this->getDefaultpcbPurchasingData() !== null) {
                    if ($salesOrderAutoBuildVersion->equals($this->getDefaultpcbPurchasingData()->getVersion())){
                        if ($this->getDefaultpcbPurchasingData() && !in_array($this->getDefaultpcbPurchasingData(), $resultPurchasingData)) {
                            array_unshift($resultPurchasingData, $this->getDefaultpcbPurchasingData());
                        }
                    }
                }
            }
        }
        return $resultPurchasingData;
    }

    /**
     * @param PurchasingData[] $purchcasingDataChoices
     */
    public function setPcbPurchasingDataChoices(array $purchcasingDataChoices)
    {
        $this->pcbPurchasingDataChoices = $purchcasingDataChoices;
    }

    public function getPcbPurchasingData()
    {
        return $this->pcbPurchasingData;
    }

    public function getPcbPurchasingDataId()
    {
        return $this->pcbPurchasingData->getId();
    }

    /**
     * @param PurchasingData $pcbPurchasingData
     */
    public function setPcbPurchasingData($pcbPurchasingData)
    {
        $this->pcbPurchasingData = $pcbPurchasingData;
    }

    public function hasPCB()
    {
        if ($this->getParentItem()->isProduct()) {
            $pkgBom = $this->getParentItem()->getBom();
            $pkgbomItems = $pkgBom->getItems();
            foreach ($pkgbomItems as $bomItem) {
                $stockItem = $bomItem->getStockItem();
                if ($stockItem->isBoard()) {
                    $brdBom = $bomItem->getComponentBom();
                    $brdbomItems = $brdBom->getItems();
                    foreach ($brdbomItems as $brdbomItem) {
                        $brdstockItem = $brdbomItem->getStockItem();
                        if ($brdstockItem->isPCB()) {
                            return true;
                        }
                    }
                }
            }
        } else if ($this->getParentItem()->isBoard()) {
            $brdBom = $this->getParentItem()->getBom();
            $brdbomItems = $brdBom->getItems();
            foreach ($brdbomItems as $bomItem) {
                $stockItem = $bomItem->getStockItem();
                if ($stockItem->isPCB()) {
                    return true;
                }
            }
        }
        return false;
    }

    /** @return StockItem|null */
    public function getPCB()
    {
        if ($this->hasPCB()) {
            if ($this->getParentItem()->isProduct()) {
                $pkgBom = $this->getParentItem()->getBom();
                $pkgbomItems = $pkgBom->getItems();
                foreach ($pkgbomItems as $bomItem) {
                    $stockItem = $bomItem->getStockItem();
                    if ($stockItem->isBoard()) {
                        $brdBom = $bomItem->getComponentBom();
                        $brdbomItems = $brdBom->getItems();
                        foreach ($brdbomItems as $brdbomItem) {
                            $brdstockItem = $brdbomItem->getStockItem();
                            if ($brdstockItem->isPCB()) {
                                return $brdstockItem;
                            }
                        }
                    }
                }
            } else if ($this->getParentItem()->isBoard()) {
                $brdBom = $this->getParentItem()->getBom();
                $brdbomItems = $brdBom->getItems();
                foreach ($brdbomItems as $bomItem) {
                    $stockItem = $bomItem->getStockItem();
                    if ($stockItem->isPCB()) {
                        return $stockItem;
                    }
                }
            }
        }
        return null;
    }

    /** @Assert\Callback */
    public function validatePurchasingData(ExecutionContextInterface $context)
    {
        /**
         * No default purchasing data is handled with {@see PurchasingDataExists}
         * on {@see SalesOrderDetailAllocator::$requirement}.
         */
        if ($this->getDefaultPurchasingData() && !$this->getPurchasingData()) {
            $context->addViolation("Cannot create work order because no purchasing data exists for rev. version at location.", [
                'version' => $this->woCreation->getVersion(),
                'location' => $this->getBuildLocation()->getName(),
            ]);
        }
    }
}

