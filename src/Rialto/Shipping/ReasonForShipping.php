<?php

namespace Rialto\Shipping;


final class ReasonForShipping
{
    const SALE = 'sale';
    const REPLACEMENT = 'replacement';
    const INTERNAL = 'internal use';

    /**
     * @return string[]
     */
    public static function all()
    {
        return [
            self::SALE => self::SALE,
            self::REPLACEMENT => self::REPLACEMENT,
            self::INTERNAL => self::INTERNAL,
        ];
    }
}
