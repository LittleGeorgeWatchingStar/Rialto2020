<?php

namespace Rialto\Shipping\Order;

use Gumstix\GeographyBundle\Model\PostalAddress;
use Rialto\Shipping\Shipper\Shipper;

/**
 * An RatableOrder implementation for API requests.
 */
class ApiRatableOrder implements RatableOrder
{
    /** @var PostalAddress */
    public $deliveryAddress;
    public $deliveryCompany;
    private $shipper;
    public $totalWeight;

    public function __construct(Shipper $shipper)
    {
        $this->shipper = $shipper;
    }

    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    public function getDeliveryCompany()
    {
        return $this->deliveryCompany;
    }

    public function getShipper()
    {
        return $this->shipper;
    }

    public function getTotalWeight()
    {
        return $this->totalWeight;
    }
}
