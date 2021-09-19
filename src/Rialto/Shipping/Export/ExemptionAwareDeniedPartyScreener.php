<?php

namespace Rialto\Shipping\Export;

use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderInterface;

/**
 * This implementation of DeniedPartyScreener checks to see if the
 * customer branch of the order is exempt from DPS checks.
 *
 * Implemented using the decorator design pattern.
 */
class ExemptionAwareDeniedPartyScreener implements DeniedPartyScreener
{
    /**
     * The decorated implementation.
     * @var DeniedPartyScreener
     */
    private $impl;

    public function __construct(DeniedPartyScreener $impl)
    {
        $this->impl = $impl;
    }

    public function isEnabled()
    {
        return $this->impl->isEnabled();
    }

    public function screen(SalesOrderInterface $order)
    {
        if ($this->isExempt($order)) {
            return new ExemptDeniedPartyResponse();
        }
        return $this->impl->screen($order);
    }

    private function isExempt(SalesOrderInterface $order)
    {
        if ($order instanceof SalesOrder) {
            $branch = $order->getCustomerBranch();
            return $branch->isDeniedPartyExempt();
        } else return false;
    }
}

class ExemptDeniedPartyResponse implements DeniedPartyResponse
{
    public function getMatchingParties()
    {
        return [];
    }

    public function hasDeniedParties()
    {
        return false;
    }
}
