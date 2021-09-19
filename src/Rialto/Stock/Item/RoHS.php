<?php

namespace Rialto\Stock\Item;

final class RoHS
{
    const COMPLIANT = 'Compliant';
    const AVAILABLE = 'Available';

    private static $valid = [
        self::COMPLIANT => self::COMPLIANT,
        self::AVAILABLE => self::AVAILABLE,
    ];

    public static function getValid()
    {
        return self::$valid;
    }

    public static function normalize($status)
    {
        return ucfirst(strtolower(trim($status)));
    }
}
