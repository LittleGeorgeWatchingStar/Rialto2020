<?php

namespace Rialto\Allocation\Requirement\RequirementTask;

use DateTime;
use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;

class RequirementTask
{
    /** @var string */
    private $fullSku;

    /** @var PurchaseOrder */
    private $purchaseOrder;

    /** @var int */
    private $qtyAllocated;

    /**
     * NOTE: null means unallocated (quantity is allocated to no stock source).
     * @var BasicStockSource|null
     */
    private $source;

    /** @var Facility */
    private $locationWhereNeeded;

    /** @var DateTime|null */
    private $estimatedArrivalDate;

    /**
     * RequirementTask constructor.
     * @param BasicStockSource|null $source
     * @param DateTime|null $estimatedArrivalDate
     */
    public function __construct(string $fullSku,
                                PurchaseOrder $purchaseOrder,
                                int $qtyAllocated,
                                $source,
                                Facility $locationWhereNeeded,
                                $estimatedArrivalDate)
    {
        $this->fullSku = $fullSku;
        $this->purchaseOrder = $purchaseOrder;
        $this->qtyAllocated = $qtyAllocated;
        $this->source = $source;
        $this->locationWhereNeeded = $locationWhereNeeded;
        $this->estimatedArrivalDate = $estimatedArrivalDate;
    }

    public function getFullSku(): string
    {
        return $this->fullSku;
    }

    public function getPurchaseOrder(): PurchaseOrder
    {
        return $this->purchaseOrder;
    }

    public function getQtyAllocated(): int
    {
        return $this->qtyAllocated;
    }

    /**
     * @return BasicStockSource|null
     */
    public function getSource()
    {
        return $this->source;
    }

    public function isWhereNeeded(): bool
    {
        return ($this->source instanceof StockBin)
            && $this->source->isAtLocation($this->getLocationWhereNeeded());
    }

    public function getLocationWhereNeeded(): Facility
    {
        return $this->locationWhereNeeded;
    }

    /**
     * @return DateTime|null
     */
    public function getCommitmentDate()
    {
        if (!$this->source instanceof StockProducer) {
            return null;
        }
        return $this->source->getCommitmentDate();
    }

    /**
     * @return DateTime|null
     */
    public function getRequestedDate()
    {
        if (!$this->source instanceof StockProducer) {
            return null;
        }
        return $this->source->getRequestedDate();
    }

    /**
     * @return DateTime|null
     */
    public function getEstimatedArrivalDate()
    {
        return $this->estimatedArrivalDate;
    }
}