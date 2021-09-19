<?php

namespace Rialto\Sales\Order\Dates;

use DateTime;
use Rialto\Accounting\Debtor\OrderAllocation;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Stock\Sku;

/**
 * When an order is paid for, this class figures out what the target ship
 * date should be.
 */
class TargetShipDateCalculator
{
    /**
     * In-stock orders paid before this time can be shipped same-day.
     */
    const SAME_DAY_CUTOFF = 15; // 3:00pm

    /**
     * @see calculateCloseEnough() below
     */
    const CLOSE_ENOUGH_ABSOLUTE = 100;

    /**
     * @see calculateCloseEnough() below
     */
    const CLOSE_ENOUGH_PERCENT = 0.95;

    public function setTargetShipDate(SalesOrder $order)
    {
        $datePaid = $this->getDatePaid($order);
        if (null === $datePaid) {
            return;
        }
        $targetDate = $this->getTargetDate($order, $datePaid);
        $order->setTargetShipDate($targetDate);
    }

    private function getDatePaid(SalesOrder $order)
    {
        $date = $this->getDateFromCardAuthorization($order);
        if (null === $date) {
            $date = $this->getDateFromReceipt($order);
        }
        return $date;
    }

    private function getDateFromCardAuthorization(SalesOrder $order)
    {
        $cardAuth = $order->getCardAuthorization();
        return $cardAuth ? $cardAuth->getDateCreated() : null;
    }

    /**
     * Find the date of the first payment that met or exceeded the
     * required deposit amount for the order.
     */
    private function getDateFromReceipt(SalesOrder $order)
    {
        $totalSoFar = 0;
        $totalNeeded = $this->calculateCloseEnough($order->getDepositAmount());
        $allocs = $order->getCreditAllocations();
        @usort($allocs, function (OrderAllocation $a, OrderAllocation $b) {
            return $a->getCreditDate()->getTimestamp() - $b->getCreditDate()->getTimestamp();
        });
        foreach ($allocs as $alloc) {
            $totalSoFar += $alloc->getAmount();
            if ($totalSoFar >= $totalNeeded) {
                return $alloc->getCreditDate();
            }
        }
        return null;
    }

    /**
     * We'll start building orders whose deposit is "close enough" to the
     * actual deposit amount.
     */
    private function calculateCloseEnough($depositAmount)
    {
        $minusFixedAmount = $depositAmount - self::CLOSE_ENOUGH_ABSOLUTE;
        $minusPercentage = self::CLOSE_ENOUGH_PERCENT * $depositAmount;
        return min($minusFixedAmount, $minusPercentage);
    }

    /**
     * @param SalesOrder $order
     * @param DateTime $datePaid
     * @return \DateTimeInterface
     */
    private function getTargetDate(SalesOrder $order, DateTime $datePaid)
    {
        $targetDate = clone $datePaid;
        if ($this->isGeppettoOrder($order)) {
            $targetDate->modify('+15 weekdays');
        } elseif ($order->getAllocationStatus()->isKitComplete()) {
            if ($this->isAfterSameDayCutoff($datePaid)) {
                $targetDate->modify('+1 weekday');
            }
        } elseif ($order->isDirectSale() && null !== $order->getDeliveryDate()) {
            $targetDate = $order->getDeliveryDate();
        } else { // standard out-of-stock lead time
            $targetDate->modify('+8 weekdays');
        }

        return $targetDate;
    }

    private function isGeppettoOrder(SalesOrder $order)
    {
        foreach ($order->getLineItems() as $item) {
            if (Sku::isGeppettoFee($item->getSku())) {
                return true;
            }
        }
        return false;
    }

    private function isAfterSameDayCutoff(DateTime $date)
    {
        $hour = (int) $date->format('H');
        return $hour >= self::SAME_DAY_CUTOFF;
    }
}
