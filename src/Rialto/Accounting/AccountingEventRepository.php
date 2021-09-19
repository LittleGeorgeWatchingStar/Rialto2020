<?php

namespace Rialto\Accounting;

use Rialto\Accounting\Transaction\SystemType;
use Rialto\Entity\RialtoEntity;

/**
 * Interface for repositories that manage accounting events.
 *
 * @see AccountingEvent
 */
interface AccountingEventRepository
{
    /**
     * @todo Should return a single entity
     * @return RialtoEntity[]
     */
    public function findByType(SystemType $sysType, $typeNo);
}
