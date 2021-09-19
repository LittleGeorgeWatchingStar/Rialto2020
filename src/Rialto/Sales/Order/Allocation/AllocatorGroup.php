<?php

namespace Rialto\Sales\Order\Allocation;


use Rialto\Database\Orm\DbManager;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Stock\Facility\Facility;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * For allocating stock to the requirements of a sales order item.
 */
class AllocatorGroup implements \IteratorAggregate, \Countable
{
    /**
     * @var SalesOrderDetailAllocator[]
     * @Assert\Valid(traverse="true")
     */
    private $allocators = [];

    public function __construct(SalesOrderDetail $lineItem, DbManager $dbm)
    {
        static $shareBins = true;

        $this->allocators = [];
        $requirements = $lineItem->getRequirements();
        foreach ($requirements as $requirement) {
            $key = $requirement->getSku();
            $allocator = SalesOrderDetailAllocator::create($requirement, $dbm);
            $allocator->setShareBins($shareBins);
            $allocator->setSelected(count($requirements) == 1);
            $this->allocators[$key] = $allocator;
        }
    }

    /**
     * @return SalesOrderDetailAllocator[]
     */
    public function getAllocators()
    {
        return $this->allocators;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->allocators);
    }

    public function count()
    {
        return count($this->allocators);
    }

    /**
     * @param Facility[] $buildLocations
     */
    public function setBuildLocations(array $buildLocations)
    {
        foreach ($this->allocators as $allocator) {
            if ($allocator->isManufactured()) {
                $allocator->setBuildLocations($buildLocations);
            }
        }
    }
}
