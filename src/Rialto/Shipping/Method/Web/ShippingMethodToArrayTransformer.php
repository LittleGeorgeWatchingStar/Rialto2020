<?php

namespace Rialto\Shipping\Method\Web;

use InvalidArgumentException;
use Rialto\Shipping\Method\ShippingMethod;
use Rialto\Shipping\Shipper\Shipper;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Transforms instances of ShippingMethod into array
 */
class ShippingMethodToArrayTransformer implements DataTransformerInterface
{
    public function transform($shippingMethod)
    {
        if (! $shippingMethod ) {
            return null;
        }
        if (! $shippingMethod instanceof ShippingMethod ) {
            throw new UnexpectedTypeException($shippingMethod, 'ShippingMethod');
        }
        return [
            'shipper' => $shippingMethod->getShipper(),
            'code' => $shippingMethod->getCode(),
        ];
    }

    public function reverseTransform($array)
    {
        if (! $this->isValid($array) ) {
            return null;
        }
        $shipper = $array['shipper'];
        assert($shipper instanceof Shipper);
        try {
            return $shipper->getShippingMethod($array['code']);
        } catch ( InvalidArgumentException $ex ) {
            throw new TransformationFailedException(
                $ex->getMessage(), $ex->getCode(), $ex);
        }
    }

    private function isValid($array)
    {
        if (! is_array($array) ) {
            return false;
        }
        return isset($array['shipper']) && isset($array['code']);
    }
}
