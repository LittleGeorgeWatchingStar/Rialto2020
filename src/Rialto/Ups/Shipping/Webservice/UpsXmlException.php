<?php

namespace Rialto\Ups\Shipping\Webservice;

use Rialto\ResourceException;
use Rialto\Shipping\ShippingException;


class UpsXmlException
extends ResourceException
implements ShippingException
{
    public function __construct(UpsXmlRequest $request, $message, \Exception $previous = null)
    {
        $message = sprintf('UPS %s request %s', $request->getName(), $message);
        if ( $previous ) {
            $message .= ': '. $previous->getMessage();
        }
        parent::__construct($message, null, $previous);
    }
}
