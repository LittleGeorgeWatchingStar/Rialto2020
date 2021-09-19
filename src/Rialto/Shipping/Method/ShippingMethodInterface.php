<?php

namespace Rialto\Shipping\Method;

interface ShippingMethodInterface
{
    /**
     * Returns the code that identifies this shipping method.
     *
     * @return string
     */
    public function getCode();

    /**
     * Returns the name of this shipping method (eg, "UPS Ground").
     *
     * @return string
     */
    public function getName();
}
