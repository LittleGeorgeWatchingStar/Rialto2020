<?php

namespace Rialto\Sales\Order;

/**
 * This event is fired when a sales quotation is confirmed into an order.
 */
class QuotationConfirmationEvent extends SalesOrderEvent
{
    private $amountPaid;

    /**
     * @param SalesOrder $order
     *  The order that was just confirmed.
     * @param double $amountPaid
     *  The total amount that the customer has paid for this order so far.
     */
    public function __construct(SalesOrder $order, $amountPaid)
    {
        parent::__construct($order);
        $this->amountPaid = $amountPaid;
    }

    public function getAmountPaid()
    {
        return $this->amountPaid;
    }
}
