<?php

namespace Rialto\Ups\Shipping;

use Rialto\Shipping\Method\ShippingMethod;
use Rialto\Shipping\Shipment\ShipmentOption;

/**
 * UPS implementation of ShipmentOption.
 */
class UpsShipmentOption implements ShipmentOption
{
    private $method;
    private $regularCost = null;
    private $discountedCost = null;

    public function __construct(ShippingMethod $method)
    {
        $this->method = $method;
    }

    /**
     * Sets the discounted cost of this shipment.
     */
    public function setDiscountedCost($cost)
    {
        $this->discountedCost = $cost;
    }

    /**
     * Sets the (non-discounted) cost of this shipment.
     */
    public function setRegularCost($cost)
    {
        $this->regularCost = $cost;
    }

    public function getShippingCost()
    {
        if ( null === $this->discountedCost ) {
            return $this->regularCost;
        }
        return $this->discountedCost;
    }

    public function getShippingMethod()
    {
        return $this->method;
    }

    public function getLabel()
    {
        return sprintf('%s ($%s)',
            $this->method->getName(),
            number_format($this->getShippingCost(), 2)
        );
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
