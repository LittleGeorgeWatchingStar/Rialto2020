<?php

namespace Rialto\Stock\Item\Version\Web;

use Rialto\Stock\Item\Version\Version;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;

/**
 * Converts instances of Version to string and vice-versa.
 */
class VersionToStringTransformer implements DataTransformerInterface
{
    public function transform($version)
    {
        if (! $version ) return null;
        if (! $version instanceof Version ) {
            throw new UnexpectedTypeException($version, 'Version');
        }
        return (string) $version;
    }

    public function reverseTransform($string)
    {
        return new Version($string);
    }
}

