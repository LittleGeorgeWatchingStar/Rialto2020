<?php

namespace Rialto\Magento2\Order;

use Rialto\Payment\GatewayException;
use UnexpectedValueException;

class PaymentException extends UnexpectedValueException implements GatewayException
{
    /** @return boolean */
    public function isTransactionNotFound()
    {
        return false;
    }
}
