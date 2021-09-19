<?php

namespace Rialto\Accounting;

use DateTime;
use Rialto\Accounting\Transaction\SystemType;

/**
 * Any event, such as a work order issuance or goods received notice, that
 * results in an accounting transaction.
 */
interface AccountingEvent
{
    /**
     * @return DateTime
     */
    public function getDate();

    /**
     * Returns a human-friendly string describing the source.
     */
    public function getMemo(): string;

    /**
     * @return SystemType
     */
    public function getSystemType();

    /**
     * @return int
     */
    public function getSystemTypeNumber();
}
