<?php

namespace Rialto\Shipping\Shipment;

use Rialto\Sales\Shipping\ShippableOrder;
use Rialto\Shipping\Method\ShippingMethodInterface;


/**
 * The default SalesOrderShipment implementation.
 *
 * This implementation is created by DefaultShipmentFactory. DefaultShipments
 * have no special logic and have a cost of zero.
 *
 * @see DefaultShipmentFactory
 */
class DefaultShipment implements SalesOrderShipment
{
    /** @var ShippableOrder */
    private $order;

    /** @var ShippingMethodInterface */
    private $method;

    private $packages = [];

    private $trackingNumber = '';

    public function __construct(ShippableOrder $order, ShippingMethodInterface $method)
    {
        $this->order = $order;
        $this->method = $method;
        $this->packages[] = new ShipmentPackage($order->getTotalWeight());
    }

    public function getComments()
    {
        return $this->order->getComments();
    }

    public function getContactPhone()
    {
        return $this->order->getContactPhone();
    }

    public function getDeliveryAddress()
    {
        return $this->order->getDeliveryAddress();
    }

    public function getDeliveryCompany()
    {
        return $this->order->getDeliveryCompany();
    }

    public function getDeliveryName()
    {
        return $this->order->getDeliveryName();
    }

    public function getLineItems()
    {
        return $this->order->getLineItems();
    }

    public function getTangibleLineItems()
    {
        return $this->order->getTangibleLineItems();
    }

    public function getOrderNumber()
    {
        return $this->order->getOrderNumber();
    }

    public function getCustomerReference(): string
    {
        return $this->order->getCustomerReference();
    }

    public function getShippingCost()
    {
        /* Default shipments cost nothing. */
        return 0.0;
    }

    public function getShippingMethod()
    {
        return $this->method;
    }

    public function getShippingPrice()
    {
        return $this->order->getShippingPrice();
    }

    public function getSubtotalPrice()
    {
        return $this->order->getSubtotalPrice();
    }

    public function getSubtotalValue()
    {
        return $this->order->getSubtotalValue();
    }

    public function getTotalWeight()
    {
        return $this->order->getTotalWeight();
    }

    public function getTrackingNumber()
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber($trackingNumber)
    {
        $this->trackingNumber = trim($trackingNumber);
    }

    public function getReasonForShipping()
    {
        return $this->order->getReasonForShipping();
    }

    public function getPackages()
    {
        return $this->packages;
    }

    public function setPackages(array $packages)
    {
        $this->packages = $packages;
    }

    public function getShipper()
    {
        return $this->order->getShipper();
    }

    public function getCode()
    {
        return $this->method->getCode();
    }

    public function getName()
    {
        return $this->method->getName();
    }

    public function shipperPaysDuties()
    {
        return $this->order->shipperPaysDuties();
    }
}
