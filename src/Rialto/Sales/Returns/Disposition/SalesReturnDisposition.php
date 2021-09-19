<?php

namespace Rialto\Sales\Returns\Disposition;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Requirement\Requirement;
use Rialto\Allocation\Requirement\SingleRequirementCollection;
use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Manufacturing\WorkOrder\WorkOrderFactory;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Sales\Returns\SalesReturnEvent;
use Rialto\Sales\Returns\SalesReturnItem;
use Rialto\Sales\SalesEvents;
use Rialto\Sales\Type\SalesType;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Bin\StockBinSplit;
use Rialto\Stock\Bin\StockBinSplitter;
use Rialto\Stock\Count\StockAdjustment;
use Rialto\Stock\Transfer\TransferService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use UnexpectedValueException;


/**
 * Processes the test results in a SalesReturnResults object.
 *
 * @see SalesReturnResults
 */
class SalesReturnDisposition
{
    /** @var DbManager */
    private $dbm;

    /** @var StockBinSplitter */
    private $splitter;

    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var AllocationFactory */
    private $allocationFactory;

    /** @var WorkOrderFactory */
    private $workOrderFactory;

    /** @var TransferService */
    private $transferSvc;

    /** @var SalesReturnInstructions */
    private $instructions = null;

    /**
     * If the disposition is "send to engineering", this field will contain
     * the sales order that we will use to send the parts to the engineer.
     *
     * @var SalesOrder
     */
    private $engSalesOrder = null;

    public function __construct(
        DbManager $dbm,
        StockBinSplitter $splitter,
        EventDispatcherInterface $dispatcher,
        AllocationFactory $allocator,
        WorkOrderFactory $woFactory,
        TransferService $transferSvc)
    {
        $this->dbm = $dbm;
        $this->splitter = $splitter;
        $this->dispatcher = $dispatcher;
        $this->allocationFactory = $allocator;
        $this->workOrderFactory = $woFactory;
        $this->transferSvc = $transferSvc;
    }

    public function dispose(SalesReturnResults $results)
    {
        foreach ($results->getItems() as $item) {
            $this->instructions = new SalesReturnInstructions($item->getSalesReturnItem());
            $this->disposeItem($item);
            $results->mergeInstructions($this->instructions);
        }
        $event = new SalesReturnEvent($results->getSalesReturn());
        $this->dispatcher->dispatch(SalesEvents::RETURN_DISPOSITION, $event);
    }

    private function disposeItem(SalesReturnItemResults $item)
    {
        $rmaItem = $item->getSalesReturnItem();
        if ($item->getQtyFailed() > 0) {
            $rmaItem->addQtyFailed($item->getQtyFailed());
            $this->processFailedStock($item);
        }
        if ($item->getQtyPassed() > 0) {
            $rmaItem->addQtyPassed($item->getQtyPassed());
            $this->processPassedStock($item);
        }
    }

    private function processFailedStock(SalesReturnItemResults $item)
    {
        $fd = $item->getFailDisposition();
        switch ($fd) {
            case SalesReturnItem::DISP_DISCARD:
                $this->discardStock($item);
                break;
            case SalesReturnItem::DISP_ENGINEERING:
                $this->allocateToEngSalesOrder(SalesReturnInstructions::STATUS_FAILED, $item);
                break;
            case SalesReturnItem::DISP_MANUFACTURER:
                $this->createReworkOrder($item);
                break;
            case SalesReturnItem::DISP_SUPPLIER:
                $this->returnToSupplier($item);
                break;
            default:
                throw new UnexpectedValueException(sprintf(
                    'Invalid fail disposition %s', $fd
                ));
        }
    }

    private function discardStock(SalesReturnItemResults $item)
    {
        $adjustment = $this->createDiscardAdjustment();
        $splitType = SalesReturnInstructions::STATUS_FAILED;
        $bin = $this->getBinToProcess($item, $splitType);
        $bin->setQtyDiff(-$item->getQtyFailed());
        $adjustment->addBin($bin);
        $this->instructions->discardBin($bin);
        $adjustment->adjust($this->dbm);
    }

    /** @return StockAdjustment */
    private function createDiscardAdjustment()
    {
        $adjustment = new StockAdjustment('Discard defective returned stock');
        $adjustment->setAdjustmentAccount($this->getDiscardAccount());
        return $adjustment;
    }

    /** @return GLAccount */
    private function getDiscardAccount()
    {
        return $this->dbm->need(GLAccount::class, GLAccount::WARRANTY_EXPENSE);
    }

    private function getBinToProcess(SalesReturnItemResults $item, string $splitType): StockBin
    {
        $origBin = $item->getStockBin();
        $qty = (SalesReturnInstructions::STATUS_FAILED == $splitType)
            ? $item->getQtyFailed()
            : $item->getQtyPassed();

        assertion($qty <= $origBin->getQuantity());
        if ($qty < $origBin->getQuantity()) {
            $newBin = $this->splitBin($origBin, $qty);
            $this->instructions->retrieveBinLabels([$newBin]);
            $this->instructions->splitBin($origBin, $newBin, $qty, $splitType);
            return $newBin;
        } else {
            return $origBin;
        }
    }

    /**
     * @param StockBin $origBin
     * @param int $qty
     * @return StockBin The new stock bin
     */
    private function splitBin(StockBin $origBin, $qty)
    {
        $split = new StockBinSplit($origBin);
        $split->setQtyToSplit($qty);
        return $this->splitter->split($split);
    }

    private function createReworkOrder(SalesReturnItemResults $item)
    {
        $qtyFailed = $item->getQtyFailed();
        $wo = $item->getReworkOrder();
        if ($wo) {
            $newQty = $wo->getQtyOrdered() + $qtyFailed;
            $wo->setQtyOrdered($newQty);
        } else {
            $wo = $this->workOrderFactory->forSalesReturn(
                $item->getSalesReturnItem(),
                $qtyFailed);
            $item->setReworkOrder($wo);
            $this->dbm->persist($wo);
        }
        $wo->appendInstructions($item->getFailureReason());
        $this->dbm->flush();
        $this->allocateToReworkOrder($wo, $item);
    }

    private function allocateToReworkOrder(WorkOrder $wo, SalesReturnItemResults $item)
    {
        $splitType = SalesReturnInstructions::STATUS_FAILED;
        $bin = $this->getBinToProcess($item, $splitType);
        $this->instructions->allocateBinToWorkOrder($bin, $wo);

        $woReq = $wo->getRequirement($item);
        $this->allocate($woReq, $bin);
    }

    private function allocate(Requirement $requirement, BasicStockSource $source)
    {
        $allocator = new SingleRequirementCollection($requirement);
        $this->allocationFactory->allocate($allocator, [$source]);
    }

    private function returnToSupplier(SalesReturnItemResults $item)
    {
        $splitType = SalesReturnInstructions::STATUS_FAILED;
        $bin = $this->getBinToProcess($item, $splitType);
        $this->instructions->returnBinToSupplier($bin);
    }

    private function processPassedStock(SalesReturnItemResults $item)
    {
        $bin = $item->getStockBin();
        if ($bin && $item->getQtyPassed() == $bin->getQuantity()) {
            $bin->setAllocatableManual(true, 'auto', 'passed RMA testing');
        }
        $pd = $item->getPassDisposition();
        switch ($pd) {
            case SalesReturnItem::DISP_CUSTOMER:
                $bin = $this->recoverStock($item);
                $this->allocateToReplacementOrder($bin, $item);
                break;
            case SalesReturnItem::DISP_STOCK:
                $this->recoverStock($item);
                break;
            case SalesReturnItem::DISP_ENGINEERING:
                $this->allocateToEngSalesOrder(SalesReturnInstructions::STATUS_PASSED, $item);
                break;
            default:
                throw new UnexpectedValueException(sprintf(
                    'Invalid pass disposition %s', $pd
                ));
        }
    }

    /**
     * @return StockBin
     *  The bin to which the working stock was moved.
     */
    private function recoverStock(SalesReturnItemResults $item)
    {
        $splitType = SalesReturnInstructions::STATUS_PASSED;
        $binToMove = $this->getBinToProcess($item, $splitType);

        $fromLoc = $item->getTestingLocation();
        $toLoc = $item->getWorkingLocation();
        $transfer = $this->transferSvc->create($fromLoc, $toLoc);
        $transfer->addBin($binToMove);
        $this->instructions->moveBin($binToMove, $toLoc);

        $this->dbm->persist($transfer);
        $this->dbm->flush();
        $this->transferSvc->kit($transfer);
        $this->transferSvc->send($transfer);
        $this->dbm->flush();
        return $binToMove;
    }

    private function allocateToReplacementOrder(
        StockBin $bin,
        SalesReturnItemResults $item)
    {
        $replacementItem = $item->getReplacementOrderItem();
        if (!$replacementItem) {
            return;
        }
        $this->instructions->allocateToReplacementOrder(
            $bin, $item->getQtyPassed(), $replacementItem->getSalesOrder()
        );
        $this->allocateToOrderItem($replacementItem, $bin);
    }

    private function allocateToEngSalesOrder(
        string $splitType,
        SalesReturnItemResults $item)
    {
        $stockItem = $item->getStockItem();
        if (!$stockItem) {
            return;
        }
        if (!$this->engSalesOrder) {
            $this->createEngineeringSalesOrder($item);
        }
        $bin = $this->getBinToProcess($item, $splitType);
        $lineItem = $this->engSalesOrder->addItem(
            $stockItem,
            $this->getEngineeringAccount(),
            $bin->getQuantity());

        // Make sure requirements are fully initialized and saved.
        $lineItem->getRequirements();
        $this->dbm->flush();

        $this->instructions->allocateToEngSalesOrder($bin, $this->engSalesOrder);
        $this->allocateToOrderItem($lineItem, $bin);
    }

    private function allocateToOrderItem(SalesOrderDetail $lineItem, StockBin $bin)
    {
        $requirements = $lineItem->getRequirements();
        assert(count($requirements) == 1);
        $requirement = reset($requirements);
        assert($requirement instanceof Requirement);
        $this->allocate($requirement, $bin);
    }

    private function createEngineeringSalesOrder(SalesReturnItemResults $item)
    {
        $salesType = SalesType::fetchDirectSale($this->dbm);
        $order = $item->createEngineeringSalesOrder($salesType);
        $this->engSalesOrder = $order;
        $this->dbm->persist($order);
    }

    private function getEngineeringAccount(): GLAccount
    {
        return $this->dbm->need(GLAccount::class, GLAccount::CHARGE_TO_ENGINEERING);
    }
}
