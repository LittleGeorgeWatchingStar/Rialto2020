<?php

namespace Rialto\Magento2;

use Rialto\Magento2\Order\SuspectedFraudEvent;

final class Magento2Events
{
    /**
     * When Magento detects that an order might be fraudulent.
     *
     * @see SuspectedFraudEvent
     */
    const SUSPECTED_FRAUD = 'magento2.suspected_fraud';
}
