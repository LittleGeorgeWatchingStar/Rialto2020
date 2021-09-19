<?php

namespace Rialto\Shopify\Order;


use Rialto\Payment\GatewayException;

class PaymentException
extends \UnexpectedValueException
implements GatewayException
{
    /** @return boolean */
    public function isTransactionNotFound()
    {
        return false;
    }

}
