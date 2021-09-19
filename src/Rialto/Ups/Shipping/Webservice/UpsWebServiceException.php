<?php

namespace Rialto\Ups\Shipping\Webservice;

use Rialto\NetworkException;
use Rialto\Shipping\ShippingException;

/**
 * Exception thrown by UPS web services.
 */
class UpsWebServiceException
extends NetworkException
implements ShippingException
{
    /* No modifications */
}
