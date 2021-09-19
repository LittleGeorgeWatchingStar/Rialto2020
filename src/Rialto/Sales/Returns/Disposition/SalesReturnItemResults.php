<?php

namespace Rialto\Sales\Returns\Disposition;

use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Sales\Returns\SalesReturnItem;
use Rialto\Sales\Type\SalesType;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Stores the results of testing a returned item.
 */
class SalesReturnItemResults extends SalesReturnItemProcessing
{
    /**
     * Where stock is kept while awaiting testing.
     * @var Facility
     */
    private $testingLoc;

    /**
     * Where stock that has passed testing is sent.
     * @var Facility
     */
    private $workingLoc;

    /**
     * @var integer
     * @Assert\NotBlank(message = "Qty passed cannot be blank")
     * @Assert\Type(type = "int", message = "Qty passed must be an integer")
     * @Assert\Range(min = "0", minMessage = "Qty passed cannot be negative")
     */
    private $qtyPassed = 0;

    /**
     * @var integer
     * @Assert\NotBlank(message = "Qty passed cannot be blank")
     * @Assert\Type(type = "int", message = "Qty passed must be an integer")
     * @Assert\Range(min = "0", minMessage = "Qty passed cannot be negative")
     */
    private $qtyFailed = 0;

    /**
     * Description of why this item failed.
     * @var string
     */
    private $failureReason = '';

    /**
     * The bin that was tested.
     * @var StockBin
     */
    private $stockBin = null;

    public function __construct(
        SalesReturnItem $rmaItem,
        Facility $testingLoc,
        Facility $workingLoc)
    {
        parent::__construct($rmaItem);
        $this->testingLoc = $testingLoc;
        $this->workingLoc = $workingLoc;
    }

    public function setQtyPassed($qty)
    {
        $this->qtyPassed = $qty;
    }

    public function getQtyPassed()
    {
        return $this->qtyPassed;
    }

    public function setQtyFailed($qty)
    {
        $this->qtyFailed = $qty;
    }

    public function getQtyFailed()
    {
        return $this->qtyFailed;
    }

    public function createEngineeringSalesOrder(SalesType $salesType): SalesOrder
    {
        $branch = $this->getEngineerBranch();
        assert(null !== $branch);
        $order = new SalesOrder($branch);
        $rma = $this->getSalesReturn();
        $createdBy = $rma->getAuthorizedBy();
        $order->setCreatedBy($createdBy);
        $order->setSalesType($salesType);
        $order->setComments("Engineering sales order for $rma");
        $order->setSalesStage(SalesOrder::ORDER);
        $order->setShipFromFacility($this->getTestingLocation());
        return $order;
    }

    private function getEngineerBranch()
    {
        return $this->rmaItem->getEngineerBranch();
    }

    public function getFailureReason()
    {
        return $this->failureReason;
    }

    public function setFailureReason($failureReason)
    {
        $this->failureReason = trim($failureReason);
    }

    public function setStockBin(StockBin $bin = null)
    {
        $this->stockBin = $bin;
    }

    public function getStockBin()
    {
        return $this->stockBin;
    }

    public function getFailDisposition()
    {
        return $this->rmaItem->getFailDisposition();
    }

    public function getPassDisposition()
    {
        return $this->rmaItem->getPassDisposition();
    }

    /**
     * The location where items undergo testing.
     * @return Facility
     */
    public function getTestingLocation()
    {
        return $this->testingLoc;
    }

    /**
     * The location to which working items should be sent.
     *
     * @return Facility
     */
    public function getWorkingLocation()
    {
        return $this->workingLoc;
    }

    /**
     * The rework order that will repair this item if it fails testing.
     *
     * @return WorkOrder
     */
    public function getReworkOrder()
    {
        return $this->rmaItem->getReworkOrder();
    }

    public function setReworkOrder(WorkOrder $order)
    {
        $this->rmaItem->setReworkOrder($order);
    }

    /**
     * The item in the replacement order (if any) to replace this item.
     *
     * @return SalesOrderDetail|null
     */
    public function getReplacementOrderItem()
    {
        return $this->rmaItem->getReplacementOrderItem();
    }

    /**
     * @Assert\Callback
     */
    public function assertQuantityValid(ExecutionContextInterface $context)
    {
        $qtyRecd = $this->rmaItem->getQtyReceived();
        $prevTested = $this->rmaItem->getQtyTested();
        $testedNow = $this->qtyPassed + $this->qtyFailed;
        $totalTested = $prevTested + $testedNow;
        if ($totalTested > $qtyRecd) {
            $context->addViolation(
                "You cannot test more items ($totalTested) than have " .
                "been received ($qtyRecd).");
        }
    }

    /**
     * @Assert\Callback
     */
    public function assertBinValid(ExecutionContextInterface $context)
    {
        $testedNow = $this->qtyPassed + $this->qtyFailed;
        if ($testedNow <= 0) return;

        if (! $this->stockBin) {
            $context->buildViolation("No bin selected.")->atPath('stockBin')->addViolation();
            return;
        }

        if (! $this->stockBin->isAtLocation($this->testingLoc)) {
            throw new \UnexpectedValueException(sprintf(
                'Selected bin %s is not at location %s',
                $this->stockBin->getId(), $this->testingLoc->getName()
            ));
        }
        $binTotal = $this->stockBin->getQuantity();

        if ($testedNow != $binTotal) {
            $context->buildViolation(
                "The quantity tested ($testedNow) does not match " .
                "the quantity in the selected bin ($binTotal)."
            )
                ->atPath('stockBin')
                ->addViolation();
        }
    }

    /**
     * @Assert\Callback
     */
    public function validateFailureReason(ExecutionContextInterface $context)
    {
        if (($this->qtyFailed > 0) && (! $this->failureReason)) {
            $context->buildViolation("Please provide a failure reason.")
                ->atPath('failureReason')
                ->addViolation();
        }
    }
}
