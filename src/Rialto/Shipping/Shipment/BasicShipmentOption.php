<?php

namespace Rialto\Shipping\Shipment;

use Rialto\Shipping\Method\ShippingMethodInterface;

/**
 * Basic implementation of ShipmentOption.
 */
class BasicShipmentOption implements ShipmentOption
{
    private $method;
    private $cost;

    function __construct(ShippingMethodInterface $method, $cost)
    {
        $this->method = $method;
        $this->cost = $cost;
    }

    public function getShippingCost()
    {
        return $this->cost;
    }

    public function getShippingMethod()
    {
        return $this->method;
    }

    public function getCode()
    {
        return $this->method->getCode();
    }

    public function getName()
    {
        return $this->method->getName();
    }
}
