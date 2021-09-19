<?php

namespace Rialto\Shipping\Export;

use Rialto\Sales\Order\SalesOrderInterface;

/**
 * Screens orders to see if they match denied parties.
 *
 * A "denied party" is a person or organization to whom the government
 * has prohibited shipments; for example, terrorist organizations.
 */
interface DeniedPartyScreener
{
    /** @return boolean */
    public function isEnabled();

    /** @return DeniedPartyResponse */
    public function screen(SalesOrderInterface $order);
}
