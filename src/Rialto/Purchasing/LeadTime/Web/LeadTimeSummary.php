<?php

namespace Rialto\Purchasing\LeadTime\Web;


use Rialto\Purchasing\LeadTime\LeadTime;

class LeadTimeSummary
{
    /** @var LeadTime */
    private $lt;

    public function __construct(LeadTime $leadTime)
    {
        $this->lt = $leadTime;
    }

    public function getSku(): string
    {
        return $this->lt->getSku();
    }

    public function getOrderQty()
    {
        return $this->lt->getOrderQty();
    }

    public function getTotalDays(): int
    {
        return $this->lt->getTotalDays();
    }
}
