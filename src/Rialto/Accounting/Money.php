<?php

namespace Rialto\Accounting;


class Money
{
    /**
     * Allows us to change the rounding mode for all financial transactions,
     * should the need arise.
     *
     * @see http://php.net/manual/en/function.round.php
     * @see http://en.wikipedia.org/wiki/Rounding#Tie-breaking
     */
    public static function round($amount, $precision = 2): float
    {
        return round($amount, $precision, PHP_ROUND_HALF_UP);
    }
}
