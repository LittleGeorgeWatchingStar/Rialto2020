<?php

namespace Rialto\Shipping\Method\ShippingTimeEstimator;

use DateTime;
use Rialto\Shipping\Method\ShippingMethod;

interface ShippingTimeEstimatorInterface
{
    /**
     * @return DateTime|null
     */
    public function getEstimatedDeliveryDate(ShippingMethod $shippingMethod,
                                             DateTime $dataShipped);
}