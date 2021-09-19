<?php

namespace Rialto\Ups\Shipping\Webservice;


/**
 * An XML request to UPS that requests the available shipping methods for
 * the given order.
 */
class ShopRequest
extends RateRequestAbstract
{
    const REQUEST_OPTION = 'Shop';

    protected function getRequestOption()
    {
        return self::REQUEST_OPTION;
    }
}
