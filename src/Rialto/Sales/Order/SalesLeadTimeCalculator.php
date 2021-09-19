<?php

namespace Rialto\Sales\Order;

use Rialto\Purchasing\LeadTime\LeadTimeCalculator;

/**
 * Calculates how long it will take to have a sales order ready to ship
 * to the customer.
 */
class SalesLeadTimeCalculator
{
    /** @var LeadTimeCalculator */
    private $calculator;

    public function __construct(LeadTimeCalculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * @return int
     *  The lead time, in days, for all items in the order.
     */
    public function forSalesOrder(SalesOrder $order)
    {
        $status = $order->getAllocationStatus();
        $commitmentDate = null;
        if ($status->isKitComplete()) {
            return 1;
        } elseif ($status->isFullyAllocated()) {
            $commitmentDate = $status->getLatestCommitmentDate();
        }
        if ($commitmentDate) {
            $today = new \DateTime();
            return $this->dateDiff($today, $commitmentDate);
        } else {
            return $this->calculateLeadTimeFromPurchasingData($order);
        }
    }

    /**
     * @return int
     *  The difference between the two dates, in days.
     */
    private function dateDiff(\DateTime $d1, \DateTime $d2)
    {
        $d1->setTime(0, 0, 0);
        $d2->setTime(0, 0, 0);
        $diff = $d1->diff($d2, true);
        return $diff->days;
    }

    private function calculateLeadTimeFromPurchasingData(SalesOrder $order)
    {
        $result = 0;
        foreach ($order->getLineItems() as $lineItem) {
            foreach ($lineItem->getRequirements() as $req) {
                $lead = $this->calculator->forRequirement($req);
                $result = max($result, $lead->getTotalDays());
            }
        }
        return $result;
    }
}
