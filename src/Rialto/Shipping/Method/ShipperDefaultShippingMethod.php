<?php

namespace Rialto\Shipping\Method;

use Rialto\Shipping\Shipper\Shipper;

class ShipperDefaultShippingMethod extends ShippingMethod
{
    public function __construct(Shipper $shipper)
    {
        parent::__construct($shipper, 'default', '');
    }
}