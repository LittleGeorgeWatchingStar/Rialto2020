<?php

namespace Rialto\Sales\Returns\Receipt;

use Rialto\Sales\Order\TaxableOrder;
use Rialto\Sales\Price\PriceCalculator;
use Rialto\Sales\Returns\Disposition\SalesReturnProcessing;
use Rialto\Sales\Returns\SalesReturn;
use Rialto\Sales\Returns\SalesReturnItem;
use Rialto\Stock\Facility\Facility;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Records the receipt of items that were returned to us as part of an RMA.
 *
 * @see SalesReturn
 */
class SalesReturnReceipt
    extends SalesReturnProcessing
    implements TaxableOrder
{
    /** @var Facility */
    private $location;

    /**
     * @var SalesReturnItemReceipt[]
     * @Assert\Valid(traverse=true)
     */
    private $items = [];

    public function __construct(
        SalesReturn $rma,
        Facility $receivingLocation)
    {
        parent::__construct($rma);
        $this->location = $receivingLocation;
    }

    public function addLineItem(SalesReturnItem $rmaItem)
    {
        $this->items[] = new SalesReturnItemReceipt($this, $rmaItem);
    }

    /** @return SalesReturnItemReceipt[] */
    public function getLineItems()
    {
        return $this->items;
    }

    /**
     * @Assert\Callback
     */
    public function assertSomethingSelected(ExecutionContextInterface $context)
    {
        foreach ($this->items as $item) {
            if ($item->getQuantity() > 0) return;
        }

        $context->addViolation("Nothing selected to receive.");
    }

    /** @return float */
    public function getTotalPrice()
    {
        return $this->getSubtotalPrice() +
            $this->getTaxAmount() +
            $this->getShippingPrice();
    }

    /** @return float */
    public function getSubtotalPrice()
    {
        $total = 0;
        foreach ($this->items as $item) {
            $total += $item->getExtendedPrice();
        }
        return $total;
    }

    /** @return float */
    public function getTaxAmount()
    {
        $calculator = new PriceCalculator();
        return $calculator->calculateTaxAmount($this);
    }

    /** @return float */
    public function getShippingPrice()
    {
        /* Needed by TaxCalculator; see getTaxAmount(). Also, this might
         * change in the future. */
        return 0;
    }

    /** @return Facility */
    public function getReceivingLocation()
    {
        return $this->location;
    }
}
