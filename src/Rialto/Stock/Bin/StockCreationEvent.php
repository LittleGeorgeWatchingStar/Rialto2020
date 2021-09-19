<?php

namespace Rialto\Stock\Bin;

use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Source\StockSource;

/**
 * Fired when new stock is created.
 */
class StockCreationEvent extends StockBinEvent
{
    /**
     * The source that created the new stock.
     * @var StockSource
     */
    private $creator;

    /**
     * Any allocations that were transferred from the source to the bin.
     * @var StockAllocation[]
     */
    private $allocations = [];

    public function __construct(StockSource $creator, StockBin $bin)
    {
        parent::__construct($bin);
        $this->creator = $creator;
    }

    public function getCreator()
    {
        return $this->creator;
    }

    public function getAllocations()
    {
        return $this->allocations;
    }

    /** @param StockAllocation[] $allocations */
    public function setAllocations(array $allocations)
    {
        $this->allocations = $allocations;
    }
}
