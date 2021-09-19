<?php

namespace Rialto\Stock\Count;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Entity\RialtoEntity;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Cost\StandardCostIsSet;
use Rialto\Stock\Move\Orm\StockMoveRepository;
use Rialto\Stock\Move\StockMove;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A record of an inventory count of a particular stock bin.
 */
class BinCount implements RialtoEntity
{
    private $id;

    /** @var StockCount */
    private $stockCount;

    /**
     * The bin whose quantity is being counted.
     * @var StockBin
     */
    private $bin;

    /**
     * The quantity on the bin when the count was requested.
     */
    private $qtyAtRequest;

    /**
     * The quantity on the bin when the count was taken.
     */
    private $qtyAtCount = null;

    /**
     * The quantity counted by the inventory location manager.
     * @var integer
     * @Assert\Range(min=0, minMessage="Quantity cannot be negative.")
     */
    private $reportedQty = null;

    /**
     * The last time the location manager updated the quantities of
     * this count.
     * @var DateTime
     */
    private $dateUpdated = null;

    /** @var DateTime */
    private $dateApproved = null;

    /**
     * Allocations that should be taken into account when calculating the
     * quantity that is expected to be on the bin.
     *
     * @see getExpectedQty()
     * @var StockAllocation[]
     */
    private $selectedAllocations;

    /**
     * The quantity that the admin has accepted as the actual quantity
     * on the bin.
     * @var integer
     */
    private $acceptedQty = null;

    /**
     * @var StockMove[]
     * @see loadStockMoveHistory()
     */
    private $stockMoves = [];

    public function __construct(StockCount $stockCount, StockBin $bin)
    {
        $this->stockCount = $stockCount;
        $this->bin = $bin;
        $this->qtyAtRequest = $bin->getQtyRemaining();
        $this->selectedAllocations = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getBin()
    {
        return $this->bin;
    }

    public function isBin(StockBin $bin)
    {
        return $this->bin->equals($bin);
    }

    /**
     * @StandardCostIsSet
     */
    public function getStockItem()
    {
        return $this->bin->getStockItem();
    }

    /** @return float */
    public function getStandardCost()
    {
        return $this->getStockItem()->getStandardCost();
    }

    public function getDateRequested()
    {
        return $this->stockCount->getDateRequested();
    }

    /**
     * The quantity that Rialto thinks is currently on the bin.
     * @return integer|float
     */
    public function getCurrentQty()
    {
        return $this->bin->getQtyRemaining();
    }

    public function getQtyAtRequest()
    {
        return $this->qtyAtRequest;
    }

    public function getQtyAtCount()
    {
        return $this->qtyAtCount;
    }

    /**
     * The quantity reported by the inventory location manager.
     * @return integer|float
     */
    public function getReportedQty()
    {
        return $this->reportedQty;
    }

    public function setReportedQty($quantity)
    {
        if ( '' !== (string) $quantity ) {
            $this->reportedQty = $quantity;
            $this->qtyAtCount = $this->getCurrentQty();
            $this->dateUpdated = new DateTime();
        }
    }

    /** @return StockAllocation[] */
    public function getAllocations()
    {
        $allocs = $this->bin->getAllocations();
        return array_filter($allocs, function(StockAllocation $alloc) {
            return $alloc->getQtyAllocated() > 0;
        });
    }

    public function getSelectedAllocations()
    {
        return $this->selectedAllocations->toArray();
    }

    public function addSelectedAllocation(StockAllocation $alloc)
    {
        $this->selectedAllocations[] = $alloc;
    }

    public function removeSelectedAllocation(StockAllocation $alloc)
    {
        $this->selectedAllocations->removeElement($alloc);
    }

    public function applySelectedAllocations()
    {
        if ( $this->isApproved() ) {
            return;
        }
        if ( $this->isCounted() ) {
            $this->qtyAtCount -= $this->getSelectedAllocationQty();
        }
    }

    private function getSelectedAllocationQty()
    {
        $total = 0;
        foreach ( $this->selectedAllocations as $alloc ) {
            $total += $alloc->getQtyAllocated();
        }
        return $total;
    }

    public function getExpectedQty()
    {
        return $this->getCurrentQty() -
            $this->getSelectedAllocationQty();
    }

    public function isCounted()
    {
        return null !== $this->reportedQty;
    }

    public function getDateUpdated()
    {
        return $this->dateUpdated;
    }

    /**
     * The possible corrected current stock levels.
     *
     * Because stock changes can happen out of sequence, we can't be sure
     * what the correct current quantity is -- it depends on what other
     * stock changes affected this bin between when the count was requested,
     * entered, and approved.
     *
     * @see loadStockMoveHistory()
     * @return array(label => value)
     */
    public function getPossibleQuantities()
    {
        if (! $this->isCounted() ) { return []; }

        $possible = [];
        $this->addPossibleValue($possible, $this->reportedQty - $this->qtyAtRequest);
        $this->addPossibleValue($possible, $this->reportedQty - $this->qtyAtCount);
        $this->addPossibleValue($possible, $this->reportedQty - $this->getCurrentQty());
        $this->addPossibleValue($possible, 0); // accept the current qty
        asort($possible, SORT_NUMERIC);
        return $possible;
    }

    private function addPossibleValue(&$possible, $diff)
    {
        $current = $this->getCurrentQty();
        $value = max($current + $diff, 0);
        $diff = $value - $current;
        $label = sprintf('%s (%+d)', number_format($value), $diff);
        $possible[$label] = $value;
    }

    public function getAcceptedQty()
    {
        return $this->acceptedQty;
    }

    public function setAcceptedQty($acceptedQty)
    {
        if ( '' !== (string) $acceptedQty ) {
            $this->acceptedQty = $acceptedQty;
            $this->dateApproved = new DateTime();
        }
    }

    /** @return boolean */
    public function isApproved()
    {
        return null !== $this->acceptedQty;
    }

    /** @return boolean */
    public function needsApproval()
    {
        return $this->isCounted() && (! $this->isApproved());
    }

    public function getDateApproved()
    {
        return $this->dateApproved;
    }

    /**
     * Loads any stock moves that might have affected this count into
     * this object.
     *
     * Because any amount of time might pass between count request, entry,
     * and approval, other changes might have happened to this bin. Loading
     * the stock moves allows the admin to see these changes and decide
     * what the actual quantity should be.
     */
    public function loadStockMoveHistory(StockMoveRepository $repo)
    {
        $this->stockMoves = $repo->findForBinCount($this);
    }

    /**
     * @see loadStockMoveHistory()
     * @return StockMove[]
     */
    public function getStockMoves()
    {
        return $this->stockMoves;
    }

    /**
     * Approves the count and makes any required stock adjustments.
     *
     * @param StockAdjustment $adjustment Any stock changes will
     *   be created via this adjustment.
     */
    public function approve(StockAdjustment $adjustment)
    {
        if (! $this->isApproved() ) {
            return;
        }
        $this->bin->setNewQty($this->acceptedQty);
        $adjustment->addBin($this->bin);
    }
}
