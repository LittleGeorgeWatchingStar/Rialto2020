<?php

namespace Rialto\Shipping\Method\Web;

use Rialto\Shipping\Method\ShippingMethod;
use Rialto\Shipping\Shipper\Shipper;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;


/**
 * Transforms instances of ShippingMethod into strings and vice-versa.
 */
class ShippingMethodToStringTransformer implements DataTransformerInterface
{
    /** @var Shipper */
    private $shipper;

    public function __construct(Shipper $shipper)
    {
        $this->shipper = $shipper;
    }

    public function transform($shippingMethod)
    {
        if (! $shippingMethod ) return null;
        if (! $shippingMethod instanceof ShippingMethod ) {
            throw new UnexpectedTypeException($shippingMethod, 'ShippingMethod');
        }
        return $shippingMethod->getCode();
    }

    public function reverseTransform($string)
    {
        if (! $string ) return null;
        return $this->shipper->getShippingMethod($string);
    }
}
