<?php

namespace Rialto\Stock\Level;

use Rialto\Allocation\Requirement\RequirementCollection;
use Rialto\Allocation\Source\CompoundStockSource;
use Rialto\Database\Orm\ErpDbManager;
use Rialto\Stock\Bin\Orm\StockBinRepository;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;

/**
 * Represents the stock level for a specific version of a controlled stock
 * at a specific location.
 */
class ControlledStockLevel implements CompoundStockSource
{
    private $item;
    private $location;
    private $version;

    /**
     * This class should only be instantiated by the StockItem class.
     *
     * @param StockItem $item
     * @param Facility $location
     * @param Version $version
     */
    public function __construct(
        StockItem $item,
        Facility $location,
        Version $version )
    {
        if (! $item->isControlled() ) throw new \InvalidArgumentException(sprintf(
            'Stock item %s is not controlled', $item->getSku()
        ));
        $this->item = $item;
        $this->location = $location;
        $this->version = $version;
    }

    public function getAllocations()
    {
        $allocs = [];
        foreach ( $this->getBins() as $bin ) {
            foreach ( $bin->getAllocations() as $alloc ) {
                $allocs[] = $alloc;
            }
        }
        return $allocs;
    }

    public function getComponentSources()
    {
        return $this->getBins();
    }

    private function getBins()
    {
        $dbm = ErpDbManager::getInstance();
        /** @var $repo StockBinRepository */
        $repo = $dbm->getRepository(StockBin::class);
        return $repo->findByLocationAndItem(
            $this->location, $this->item, $this->version, true
        );
    }

    public function getQtyUnallocated()
    {
        $total = 0;
        foreach ( $this->getBins() as $bin ) {
            $total += $bin->getQtyUnallocated();
        }
        return $total;
    }

    public function getQtyAvailableTo(RequirementCollection $requirements)
    {
        $total = 0;
        foreach ( $this->getBins() as $bin ) {
            $total += $bin->getQtyAvailableTo($requirements);
        }
        return $total;
    }

    public function getQtyRemaining()
    {
        $total = 0;
        foreach ( $this->getBins() as $bin ) {
            $total += $bin->getQtyRemaining();
        }
        return $total;
    }

    public function getSku()
    {
        return $this->item->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function getStockItem()
    {
        return $this->item;
    }

    public function getVersion()
    {
        return new Version($this->version);
    }

    public function getCustomization()
    {
        return null;
    }

    public function getFullSku()
    {
        return $this->getSku() . $this->getVersion()->getStockCodeSuffix();
    }

    /** @deprecated */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    public function getLocation()
    {
        return $this->location;
    }

}
