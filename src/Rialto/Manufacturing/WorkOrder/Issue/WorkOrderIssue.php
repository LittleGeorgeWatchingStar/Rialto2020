<?php

namespace Rialto\Manufacturing\WorkOrder\Issue;

use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Accounting\AccountingEvent;
use Rialto\Accounting\Ledger\Entry\GLEntry;
use Rialto\Accounting\Period\Period;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\ErpDbManager;
use Rialto\Entity\RialtoEntity;
use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Stock\Cost\StandardCostException;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Move\StockMove;

/**
 * Records the issuance of a work order.
 */
class WorkOrderIssue implements RialtoEntity, AccountingEvent
{
    private $id;

    /** @var \DateTime */
    private $dateIssued;

    /** @var Facility */
    private $location;

    /** @var WorkOrder */
    private $workOrder;

    /** @var WorkOrderIssueItem[] */
    private $issuedItems;

    private $qtyIssued;
    private $qtyReceived = 0;

    public function __construct(WorkOrder $order, $quantity, \DateTime $date = null)
    {
        $this->workOrder = $order;
        $this->location = $order->getLocation();

        $this->qtyIssued = $quantity;
        $error = $this->validateQuantity();
        if ($error) throw new \InvalidArgumentException($error);

        $this->dateIssued = $date ?: new \DateTime();

        $this->issuedItems = new ArrayCollection();
        foreach ($this->workOrder->getRequirements() as $woReq) {
            $ii = new WorkOrderIssueItem($this, $woReq);
            if ($ii->getTotalQtyIssued() > 0) {
                $this->issuedItems[] = $ii;
            }
        }
    }

    private function validateQuantity()
    {
        if (!is_numeric($this->qtyIssued)) return "'{$this->qtyIssued}' is not a number";
        if ($this->qtyIssued < 0) return "Argument 'quantity' cannot be negative";

        $unissued = $this->workOrder->getQtyOrdered() - $this->workOrder->getQtyIssued();
        if ($this->qtyIssued > $unissued) return "Only $unissued units remain to be issued";
        return null;
    }

    public function __toString()
    {
        return sprintf('issue %s of %s on %s',
            $this->qtyIssued,
            $this->workOrder,
            $this->dateIssued->format('Y-m-d H:i:s'));
    }

    /**
     * True if any of the requirements have some quantity left to issue,
     * including scrap counts.
     */
    public function hasPartsToIssue(): bool
    {
        return count($this->issuedItems) > 0;
    }

    /** @return WorkOrderIssueItem[] */
    public function getIssuedItems()
    {
        return $this->issuedItems->toArray();
    }

    /** @return WorkOrderIssueItem|null */
    public function getIssueItem(Item $item)
    {
        foreach ($this->issuedItems as $ii) {
            if ($ii->getSku() == $item->getSku()) {
                return $ii;
            }
        }
        return null;
    }

    public function getTotalValueIssued()
    {
        $total = 0;
        foreach ($this->issuedItems as $item) {
            $total += $item->getTotalStandardCost();
        }
        return $total;
    }

    public function getUnitValueIssued()
    {
        $total = 0;
        foreach ($this->issuedItems as $item) {
            $total += ($item->getUnitQtyIssued() * $item->getUnitStandardCost());
        }
        return $total;
    }

    public function getScrapValueIssued()
    {
        $total = 0;
        foreach ($this->issuedItems as $item) {
            $total += ($item->getScrapIssued() * $item->getUnitStandardCost());
        }
        return $total;
    }

    /** @return \DateTime */
    public function getDateIssued()
    {
        return clone $this->dateIssued;
    }

    public function getDate()
    {
        return $this->getDateIssued();
    }

    /**
     * Useful for sorting by date.
     *
     * @return int
     */
    public function getTimestamp()
    {
        return $this->dateIssued->getTimestamp();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getQtyIssued()
    {
        return (float) $this->qtyIssued;
    }

    public function setQtyIssued($qty)
    {
        $this->qtyIssued = $qty;
    }

    /**
     * Used by WorkOrderIssuer to roll back an issuance.
     */
    public function reverseQtyIssued($qty)
    {
        if ($qty > $this->getQtyUnreceived()) {
            throw new \InvalidArgumentException("$qty is more than unreceived");
        }
        $this->qtyIssued -= $qty;
        assert($this->qtyIssued >= $this->qtyReceived);
    }

    public function getQtyReceived()
    {
        return (float) $this->qtyReceived;
    }

    public function addQtyReceived($qty)
    {
        $this->qtyReceived += $qty;
    }

    public function getQtyUnreceived()
    {
        return (float) ($this->qtyIssued - $this->qtyReceived);
    }

    /**
     * Returns the total stock value of a receipt for the given quantity, based
     * on the recorded standard cost of each item at the time of issue.
     *
     * @param int|float $qtyToReceive
     * @return float
     * @throws \InvalidArgumentException
     *  If $qtyToReceive is greater than the quantity still unreceived.
     */
    public function calculateValueOfReceipt($qtyToReceive)
    {
        if ($qtyToReceive > $this->getQtyUnreceived()) {
            throw new \InvalidArgumentException(sprintf(
                'Qty to receive (%s) cannot be greater than qty unreceived (%s)',
                $qtyToReceive, $this->getQtyUnreceived()
            ));
        }

        $value = 0.0;
        foreach ($this->getIssuedItems() as $item) {
            $value += $this->calculateValueOfItemReceived($item, $qtyToReceive);
        }
        return $value;
    }

    private function calculateValueOfItemReceived(
        WorkOrderIssueItem $item,
        $qtyToReceive)
    {
        $qtyToCount = $qtyToReceive * $item->getUnitQtyIssued();
        if ($this->qtyReceived == 0) {
            /* Include the value of scrap on the first receipt */
            $qtyToCount += $item->getScrapIssued();
        }
        if ($item->getUnitStandardCost() <= 0) {
            throw new StandardCostException(
                $item->getSku(), $item->getUnitStandardCost());
        }
        return $item->getUnitStandardCost() * $qtyToCount;
    }

    /**
     * @return string
     */
    public function getMemo(): string
    {
        return sprintf('Issued %s x %s from WO %s',
            number_format($this->qtyIssued),
            $this->workOrder->getSku(),
            $this->workOrder->getId()
        );
    }

    /**
     * @return Period
     */
    public function getPeriod()
    {
        return Period::fetchForDate($this->getDate());
    }

    /**
     * @return SystemType
     */
    public function getSystemType()
    {
        return SystemType::fetchWorkOrderIssue();
    }

    /**
     * @return int
     */
    public function getSystemTypeNumber()
    {
        return $this->getId();
    }

    /** @return WorkOrder */
    public function getWorkOrder()
    {
        return $this->workOrder;
    }

    public function getGLEntries()
    {
        $dbm = ErpDbManager::getInstance();
        $repo = $dbm->getRepository(GLEntry::class);
        return $repo->findByEvent($this);
    }

    /** @return StockMove[] */
    public function getStockMoves()
    {
        $dbm = ErpDbManager::getInstance();
        $repo = $dbm->getRepository(StockMove::class);
        return $repo->findByEvent($this);
    }

    /**
     * @return Requirement
     */
    public function getRequirement(Item $item)
    {
        return $this->workOrder->getRequirement($item);
    }
}
