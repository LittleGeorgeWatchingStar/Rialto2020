<?php

namespace Rialto\Stock\Consumption;

use InvalidArgumentException;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Allocation\Allocation\AllocationValidator;
use Rialto\Allocation\Allocation\InvalidAllocationException;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Requirement\Requirement;
use Rialto\Exception\InvalidDataException;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Move\StockMove;

/**
 * Stock "consumption" refers to the process of stock being used up for some
 * purpose; eg: sent to the customer as product, used in the manufacture of
 * some other item, etc.
 */
class StockConsumption
{
    /**
     * @var Requirement
     */
    private $requirement;

    /**
     * @var StockItem
     */
    private $item;

    /**
     * @var Transaction
     */
    private $transaction;

    /** @var AllocationValidator */
    private $validator;

    public function __construct(
        Requirement $requirement,
        Transaction $transaction )
    {
        $this->requirement = $requirement;
        $this->item = $requirement->getStockItem();
        assertion($this->item->isPhysicalPart());
        $this->transaction = $transaction;
        $this->validator = new AllocationValidator();
    }

    /**
     * Consumes $qty units of the requested stock item.
     *
     * @param int|double $qty
     * @return StockMove[] The stock moves that were created.
     */
    public function consume($qty)
    {
        $required = $this->requirement->getTotalQtyOrdered();
        if ( $qty > $required ) {
            throw new InvalidArgumentException(sprintf(
                'Cannot consume more (%s) than the required amount (%s) of %s.',
                number_format($qty),
                number_format($required),
                $this->item->getSku()
            ));
        }
        return $this->consumeAllocations($qty);
    }

    /** @return StockMove[] */
    private function consumeAllocations($qty)
    {
        $srcLoc = $this->requirement->getFacility();

        /* Prepare to mark allocations as delivered. */
        $allocations = $this->requirement->getAllocations();
        $stillToMove = $qty;
        $moves = [];

        foreach ( $allocations as $alloc ) {
            $this->validateAllocation($alloc);

            if ( $stillToMove <= 0 ) break;
            if ( $alloc->isDelivered() ) continue;

            /* Only use allocations that are from the correct location. */
            if (! $alloc->isFromStock() ) continue;
            if (! $alloc->isAtLocation($srcLoc) ) continue;

            /** @var $bin StockBin */
            $bin = $alloc->getSource();
            assert( $bin instanceof StockBin );

            /* How much can we deliver from this allocation? */
            $allocAvailable = $alloc->getQtyAllocated();
            $toMove = min($stillToMove, $allocAvailable);

            /* Update the allocation */
            $alloc->addQtyDelivered($toMove);

            /* Update the source */
            $bin->setQtyDiff(-$toMove);
            assert( $bin->getNewQty() >= 0);
            $moves[] = $bin->applyNewQty($this->transaction);

            $stillToMove -= $toMove;
        }

        if ( $stillToMove > 0 ) {
            throw new InvalidDataException(sprintf(
                '%s of %s units of %s are still unallocated at %s',
                number_format($stillToMove),
                number_format($qty),
                $this->item->getSku(),
                $srcLoc->getName()
            ));
        }

        return $moves;
    }

    private function validateAllocation(StockAllocation $alloc)
    {
        if (! $this->validator->isValid($alloc) ) {
            throw new InvalidAllocationException($alloc, $this->validator->getMessages());
        }
    }
}

