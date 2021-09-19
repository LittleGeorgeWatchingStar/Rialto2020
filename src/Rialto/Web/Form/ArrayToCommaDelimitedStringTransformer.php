<?php

namespace Rialto\Web\Form;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\UnexpectedTypeException;


/**
 * Converts arrays into comma-delimited strings, and vice-versa.
 */
class ArrayToCommaDelimitedStringTransformer implements DataTransformerInterface
{
    /**
     * Converts an array of strings into a single comma-delimited string.
     *
     * @param string[] $array
     * @return string
     */
    public function transform($array)
    {
        if (! $array ) return '';
        if (! is_array($array) ) {
            throw new UnexpectedTypeException($array, 'array');
        }
        sort($array);
        return join(',', array_filter($array));
    }

    /**
     * Converts a comma-delimited string into an array of strings.
     *
     * @param string $string
     * @return string[]
     */
    public function reverseTransform($string)
    {
        $list = explode(',', $string);
        return array_values(array_filter(array_map('trim', $list)));
    }

}
