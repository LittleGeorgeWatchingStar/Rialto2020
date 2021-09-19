<?php

namespace Rialto\Manufacturing\WorkOrder\Issue;

use LogicException;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Entity\RialtoEntity;
use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Cost\StandardCostException;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;

class WorkOrderIssueItem implements RialtoEntity, Item
{
    private $id;

    /** @var StockItem */
    private $stockItem;

    /** @var WorkOrderIssue */
    private $workOrderIssue;

    /**
     * The number of units issued per unit of the parent item.
     * @var int
     */
    private $unitQtyIssued;

    /**
     * The number of units of scrap issued.
     * @var int
     */
    private $scrapIssued;

    /**
     * The standard cost per unit of this component at the time of issue.
     * @var float
     */
    private $unitStandardCost;

    public function __construct(
        WorkOrderIssue $issue,
        Requirement $woReq)
    {
        $this->workOrderIssue = $issue;
        $this->stockItem = $woReq->getStockItem();
        $this->unitQtyIssued = $woReq->getUnitQty();
        $this->scrapIssued = $woReq->getScrapUnissued();
        $this->unitStandardCost = $this->stockItem->getStandardCost();
        if ( $this->unitStandardCost <= 0 ) {
            throw new StandardCostException($this->stockItem, $this->unitStandardCost);
        }
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /** @return WorkOrderIssue */
    public function getIssue()
    {
        return $this->workOrderIssue;
    }

    /** @return Facility */
    public function getIssueLocation()
    {
        return $this->workOrderIssue->getWorkOrder()->getLocation();
    }

    /** @return StockItem */
    public function getStockItem()
    {
        return $this->stockItem;
    }

    public function getSku()
    {
        return $this->stockItem->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function getUnitQtyIssued()
    {
        return $this->unitQtyIssued;
    }

    /**
     * @deprecated
     * This is just needed to repair existing records.
     */
    public function setUnitQtyIssued($unitQtyIssued)
    {
        $this->unitQtyIssued = $unitQtyIssued;
    }

    public function getScrapIssued()
    {
        return $this->scrapIssued;
    }

    public function setScrapIssued($qty)
    {
        $this->scrapIssued = $qty;
    }

    public function getTotalQtyIssued()
    {
        return ($this->unitQtyIssued * $this->workOrderIssue->getQtyIssued())
            + $this->scrapIssued;
    }

    /** @return GLAccount */
    public function getStockAccount()
    {
        return $this->stockItem->getStockAccount();
    }

    public function getUnitStandardCost()
    {
        return (float) $this->unitStandardCost;
    }

    /**
     * @deprecated Do not use this method - it is only for repair purposes.
     */
    public function setUnitStandardCost($cost)
    {
        if ( $this->unitStandardCost > 0 ) {
            throw new LogicException("Unit std cost is already set");
        }
        $this->unitStandardCost = $cost;
    }

    /**
     * Returns the total value of stock issued.
     * @return double
     */
    public function getTotalStandardCost()
    {
        return $this->getUnitStandardCost() * $this->getTotalQtyIssued();
    }

    /**
     * Returns the bins from which this item was issued.
     * @return StockBin[]
     */
    public function getIssuedBins()
    {
        $bins = [];
        foreach ( $this->workOrderIssue->getStockMoves() as $move ) {
            if ( $move->getSku() == $this->getSku() ) {
                $bin = $move->getStockBin();
                $bins[$bin->getId()] = $bin;
            }
        }
        return $bins;
    }

    public function getTotalQtyIssuedFromBin(StockBin $bin)
    {
        $total = 0;
        foreach ( $this->workOrderIssue->getStockMoves() as $move ) {
            if ( $bin->equals($move->getStockBin()) ) {
                /* Remember: issuance stock moves have a negative qty. */
                $total -= $move->getQuantity();
            }
        }
        return $total;
    }

    /** @return Requirement */
    public function getRequirement()
    {
        return $this->workOrderIssue->getRequirement($this->stockItem);
    }

    /** @return Version */
    public function getVersion()
    {
        return $this->getRequirement()->getVersion();
    }

    public function getCustomization()
    {
        return $this->getRequirement()->getCustomization();
    }

    /** @return StockAllocation[] */
    public function getAllocations()
    {
        return $this->getRequirement()->getAllocations();
    }

    /**
     * @return StockAllocation|null
     */
    public function getAllocation(BasicStockSource $source)
    {
        foreach ( $this->getAllocations() as $alloc ) {
            if ( $source->equals($alloc->getSource())) {
                return $alloc;
            }
        }
        return null;
    }
}
