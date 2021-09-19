<?php

namespace Rialto\Measurement\Web;

use Rialto\Measurement\Dimensions;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

class DimensionsToArrayTransformer implements DataTransformerInterface
{
    public function reverseTransform($array)
    {
        if ($this->isEmpty($array)) return null;
        return new Dimensions($array['x'], $array['y'], $array['z']);
    }

    private function isEmpty($array)
    {
        if (empty($array)) return true;
        if (!is_array($array)) {
            throw new UnexpectedTypeException($array, 'array');
        }
        foreach (['x', 'y', 'z'] as $axis) {
            if (!empty($array[$axis])) return false;
        }
        return true;
    }

    public function transform($dimensions)
    {
        if (!$dimensions) return [];
        if (!$dimensions instanceof Dimensions) {
            throw new UnexpectedTypeException($dimensions, "Dimensions");
        }
        return $dimensions->toArray();
    }

}
