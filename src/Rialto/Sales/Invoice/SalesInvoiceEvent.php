<?php

namespace Rialto\Sales\Invoice;

use Rialto\Sales\Order\SalesOrderEvent;
use Rialto\Shipping\Shipment\SalesOrderShipment;

/**
 * This event is dispatched when a sales order is invoiced and shipped.
 */
class SalesInvoiceEvent extends SalesOrderEvent
{
    /** @var SalesInvoice */
    private $invoice;

    /** @var SalesOrderShipment */
    private $shipment = null;

    public function __construct(SalesInvoice $invoice)
    {
        parent::__construct($invoice->getSalesOrder());
        $this->invoice = $invoice;
    }

    public function setShipment(SalesOrderShipment $shipment)
    {
        $this->shipment = $shipment;
    }

    /** @return SalesInvoice */
    public function getInvoice()
    {
        return $this->invoice;
    }

    /** @return SalesOrderShipment|null */
    public function getShipment()
    {
        return $this->shipment;
    }
}
