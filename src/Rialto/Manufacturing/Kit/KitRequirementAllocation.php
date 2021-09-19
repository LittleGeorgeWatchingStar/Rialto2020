<?php

namespace Rialto\Manufacturing\Kit;

use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Source\StockSource;
use Rialto\Database\Orm\DbManager;
use Rialto\Stock\Bin\Orm\StockBinRepository;
use Rialto\Stock\Bin\StockBin;

/**
 * Provides an interface for allocating stock to a work order kit
 * requirement.
 *
 * This is the class to which the allocation forms on the kitting page are
 * bound.
 *
 * @see WorkOrderKitRequirement
 */
class KitRequirementAllocation
{
    /** @var DbManager */
    private $dbm;

    /** @var KitRequirement */
    private $kitReq;

    /** @var StockSource[] */
    private $allSources;

    /** @var StockSource[] */
    private $selectedSources;

    public function __construct(
        DbManager $dbm,
        KitRequirement $kitReq)
    {
        $this->dbm = $dbm;
        $this->kitReq = $kitReq;
        $this->allSources = $this->loadPossibleSources();
        $this->selectedSources = $this->loadSelectedSources();
    }

    /** @return StockSource[] */
    private function loadPossibleSources()
    {
        /** @var $repo StockBinRepository */
        $repo = $this->dbm->getRepository(StockBin::class);
        return $repo->findByParentLocationAndItem(
            $this->kitReq->getOrigin(),
            $this->kitReq
        );
    }

    /** @return StockSource[] */
    private function loadSelectedSources()
    {
        $allocs = $this->kitReq->getAllocationsAtOrigin();
        $bins = [];
        foreach ($allocs as $alloc) {
            $bin = $alloc->getSource();
            $bins[$bin->getId()] = $bin;
        }
        return $bins;
    }

    public function getBins()
    {
        return $this->selectedSources;
    }

    public function addBin(StockBin $bin)
    {
        $this->selectedSources[$bin->getId()] = $bin;
    }

    public function removeBin(StockBin $bin)
    {
        unset($this->selectedSources[$bin->getId()]);
    }

    public function getPossibleBins()
    {
        return $this->allSources;
    }

    public function getBinOptions()
    {
        $options = [];
        foreach ($this->allSources as $bin) {
            $qtyAvailable = $this->kitReq->getQtyAvailableFromSource($bin);
            $qtyAllocated = $this->kitReq->getQtyAllocatedFromSource($bin);
            $grossAvailable = $qtyAvailable + $qtyAllocated;

            if ($grossAvailable <= 0) {
                continue;
            }
            $options[] = $bin;
        }
        return $options;
    }

    public function autoAllocate(AllocationFactory $factory)
    {
        $this->allocateFrom($this->allSources, $factory);
    }

    public function allocateFromSelected(AllocationFactory $factory)
    {
        $this->allocateFrom($this->selectedSources, $factory);
    }

    private function allocateFrom(array $sources, AllocationFactory $factory)
    {
        $this->kitReq->reallocateFromSources($sources, $factory);
    }
}
