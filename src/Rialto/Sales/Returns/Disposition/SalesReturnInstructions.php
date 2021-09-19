<?php

namespace Rialto\Sales\Returns\Disposition;

use Rialto\Allocation\Source\StockSource;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Returns\SalesReturnItem;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;


/**
 * Keeps track of the instructions that the warehouse staff need
 * when processing tested RMA items.
 */
class SalesReturnInstructions
{
    const STATUS_PASSED = 'working';
    const STATUS_FAILED = 'defective';
    const STATUS_RECEIVED = 'received';

    /** @var SalesReturnItem */
    private $rmaItem;

    /**
     * @var string[]
     *  A list of instructions to render on-screen.
     */
    private $instructions = [];

    /**
     * @var string[]
     *  A list of instructions to print on labels.
     */
    private $labels = [];

    /**
     * @param SalesReturnItem $rmaItem
     */
    public function __construct(SalesReturnItem $rmaItem)
    {
        $this->rmaItem = $rmaItem;
    }

    public function discardBin(StockBin $bin)
    {
        $this->labels[] = $this->instructions[] = sprintf(
            'Discard bin %s.', $bin->getId()
        );
    }

    public function discardStock($qty)
    {
        $this->labels[] = $this->instructions[] = sprintf(
            'Discard defective %s (%s units).',
            $this->rmaItem->getSku(),
            number_format($qty)
        );
    }

    public function retrieveBinLabels(array $bins)
    {
        $ids = array_map(function (StockBin $bin) {
            return $bin->getId();
        }, $bins);

        $this->instructions[] = sprintf(
            'Retrieve labels for the following bins: %s.',
            join(', ', $ids)
        );
    }

    public function splitBin(
        StockBin $oldBin, StockBin $newBin, $qty, $splitType)
    {
        $this->instructions[] = sprintf(
            'Remove %s %s (%s units) from bin %s and put them into bin %s.',
            $splitType,
            $this->rmaItem->getSku(),
            number_format($qty),
            $oldBin->getId(),
            $newBin->getId()
        );
    }

    public function allocateBinToWorkOrder(StockBin $bin, WorkOrder $wo)
    {
        $this->labels[] = $this->instructions[] = sprintf(
            'Set aside bin %s for work order %s.',
            $bin->getId(),
            $wo->getId()
        );
    }

    public function allocateStockToWorkOrder($qty, WorkOrder $wo)
    {
        $this->labels[] = $this->instructions[] = sprintf(
            'Set aside defective %s (%s units) for work order %s.',
            $this->rmaItem->getSku(),
            number_format($qty),
            $wo->getId()
        );
    }

    public function allocateToEngSalesOrder(StockBin $bin, SalesOrder $order)
    {
        $this->labels[] = $this->instructions[] = sprintf(
            'Set aside bin %s for engineering sales order %s.',
            $bin->getId(),
            $order->getId()
        );
    }

    public function allocateToReplacementOrder(
        StockSource $src, $qty, SalesOrder $order)
    {
        if ($src instanceof StockBin) {
            $this->labels[] = $this->instructions[] = sprintf(
                'Set aside bin %s for sales order %s.',
                $src->getId(),
                $order->getId()
            );
        } else {
            $this->labels[] = $this->instructions[] = sprintf(
                'Set aside working %s (%s units) for sales order %s.',
                $this->rmaItem->getSku(),
                number_format($qty),
                $order->getId()
            );
        }
    }

    public function moveBins(array $bins, Facility $destination)
    {
        foreach ($bins as $bin) {
            $this->moveBin($bin, $destination);
        }
    }

    public function moveBin(StockBin $bin, Facility $destination)
    {
        $this->instructions[] = sprintf(
            'Move bin %s to %s.',
            $bin->getId(),
            $destination->getName()
        );
    }

    public function moveReceivedStock($qty, Facility $destination)
    {
        $this->moveStock($qty, $destination, self::STATUS_RECEIVED);
    }

    public function moveWorkingStock($qty, Facility $destination)
    {
        $this->moveStock($qty, $destination, self::STATUS_PASSED);
    }

    private function moveStock($qty, Facility $destination, $status)
    {
        $this->instructions[] = sprintf(
            'Move %s %s (%s units) to %s.',
            $status,
            $this->rmaItem->getSku(),
            number_format($qty),
            $destination->getName()
        );
    }

    public function returnBinToSupplier(StockBin $bin)
    {
        $this->labels[] = $this->instructions[] = sprintf(
            'Set aside bin %s for return to the supplier.',
            $bin->getId()
        );
    }

    public function returnStockToSupplier($qty)
    {
        $this->labels[] = $this->instructions[] = sprintf(
            'Set aside defective %s (%s units) for return to the supplier.',
            $this->rmaItem->getSku(),
            number_format($qty)
        );
    }

    public function sendBinToEngineering(StockBin $bin)
    {
        $this->labels[] = $this->instructions[] = sprintf(
            'Set aside bin %s for the engineering staff.',
            $bin->getId()
        );
    }

    public function sendStockToEngineering($qty)
    {
        $this->labels[] = $this->instructions[] = sprintf(
            'Set aside working %s (%s units) for the engineering staff.',
            $this->rmaItem->getSku(),
            number_format($qty)
        );
    }

    public function merge(SalesReturnInstructions $other)
    {
        $this->instructions = array_merge(
            $this->instructions,
            $other->instructions
        );
        $this->labels = array_merge(
            $this->labels,
            $other->labels
        );
    }

    /** @return string[] */
    public function toArray()
    {
        return $this->instructions;
    }

    /** @return string[] */
    public function getLabels()
    {
        return $this->labels;
    }
}
