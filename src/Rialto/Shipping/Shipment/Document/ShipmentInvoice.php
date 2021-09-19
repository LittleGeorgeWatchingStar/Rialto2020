<?php

namespace Rialto\Shipping\Shipment\Document;

use Rialto\Sales\Order\SalesOrderInterface;

/**
 * The invoice that is required to accompany all international shipments.
 */
class ShipmentInvoice
{
    const CURRENCY_CODE_USD = 'USD';

    /** @var SalesOrderInterface */
    private $order;

    public function __construct(SalesOrderInterface $order)
    {
        $this->order = $order;
    }

    public function isRequired()
    {
        $address = $this->order->getDeliveryAddress();
        return $address->getCountryCode() != 'US';
    }

    public function getOrderNumber()
    {
        return $this->order->getOrderNumber();
    }

    public function getLineItems()
    {
        return $this->order->getTangibleLineItems();
    }

    public function getReasonForExport()
    {
        return $this->order->getReasonForShipping();
    }

    public function getComments()
    {
        return $this->order->getComments();
    }

    public function getShippingPrice()
    {
        return $this->order->getShippingPrice();
    }

    public function getCurrencyCode()
    {
        return self::CURRENCY_CODE_USD;
    }
}
