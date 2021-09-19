<?php

namespace Rialto\Payment;

/**
 * Exceptions thrown by payment gateways.
 */
interface GatewayException
{
    /** @return boolean */
    public function isTransactionNotFound();
}
