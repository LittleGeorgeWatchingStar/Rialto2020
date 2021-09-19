<?php

namespace Rialto\Manufacturing\Component;


final class Designators
{
    /**
     * Normalizes a list of reference designators to make sure
     * each one is properly formatted.
     *
     * @param string[] $designators
     * @return string[]
     */
    public static function normalize(array $designators)
    {
        $designators = array_map('strtoupper', $designators);
        $designators = array_map('trim', $designators);
        $designators = array_unique($designators);
        $designators = array_filter($designators);
        return array_values($designators);
    }

    /**
     * All designators of $a that are not in $b.
     *
     * @param string[] $a
     * @param string[] $b
     * @return string[]
     */
    public static function setDiff(array $a, array $b)
    {
        return self::normalize(array_diff($a, $b));
    }

    /**
     * The union of $a and $b.
     *
     * @param string[] $a
     * @param string[] $b
     * @return string[]
     */
    public static function setUnion(array $a, array $b)
    {
        return self::normalize(array_merge($a, $b));
    }
}
