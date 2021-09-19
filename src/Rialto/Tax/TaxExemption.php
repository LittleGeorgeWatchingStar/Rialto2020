<?php

namespace Rialto\Tax;

/**
 * Possible tax exemption statuses.
 */
final class TaxExemption
{
    const NONE = 'Taxable';
    const FEDERAL = 'Federal';
    const RESALE = 'Resale';

    /**
     * All possible tax exemption statuses.
     * @return string[]
     */
    public static function getChoices()
    {
        $all = [
            self::NONE,
            self::FEDERAL,
            self::RESALE,
        ];
        return array_combine($all, $all);
    }

    public static function isExempt($status)
    {
        return in_array($status, self::exemptStatuses());
    }

    public static function exemptStatuses()
    {
        return [
            self::FEDERAL,
            self::RESALE,
        ];
    }
}
