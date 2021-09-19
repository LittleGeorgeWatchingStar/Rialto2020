<?php

namespace Rialto\Purchasing\Order;

/**
 * A purchase initiator creates purchase orders.
 */
interface PurchaseInitiator
{
    /**
     * Returns a string that is used to identify the purchase initiator.
     *
     * @return string
     */
    public function getInitiatorCode();

}
