<?php

namespace Rialto\Sales\Discount;

use Rialto\Database\Orm\DbManager;
use Rialto\Sales\Discount\Orm\DiscountGroupRepository;
use Rialto\Sales\Discount\Orm\DiscountRateRepository;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderDetail;

class DiscountCalculator
{
    /** @var DbManager */
    private $dbm;

    /** @var DiscountGroupRepository */
    private $groups;

    /** @var DiscountRateRepository */
    private $rates;

    const OTHER = "other";

    public function __construct(DbManager $dbm)
    {
        $this->dbm = $dbm;
        $this->groups = $dbm->getRepository(DiscountGroup::class);
        $this->rates = $dbm->getRepository(DiscountRate::class);
    }

    /**
     * Updates the discount levels of the line items in the sales order.
     * @param SalesOrder $order
     */
    public function updateDiscounts(SalesOrder $order)
    {
        $items = $order->getLineItems();
        $discounts = $this->calculate($items);

        foreach ( $items as $item ) {
            $discount = $this->getDiscount($discounts, $item);
            $item->setDiscountRate($discount);
        }
    }

    /**
     * Takes a list of sales order line items and calculates what the
     * discounts should be.
     *
     * @param $lineItems SalesOrderDetail[]
     * @return float[]
     */
    private function calculate(array $lineItems)
    {
        $quantities = new \SplObjectStorage();
        $maxQty = 0;
        $maxQtyGroup = null;
        foreach ($lineItems as $item)
        {
            $group = $this->groups->findByItem($item);
            if ( ! $group ) continue;

            if ( ! isset($quantities[$group])) {
                $quantities[$group] = 0;
            }
            $quantities[$group] += $item->getQtyOrdered();

            if ($quantities[$group] > $maxQty) {
                $maxQty = $quantities[$group];
                $maxQtyGroup = $group;
            }
        }

        $discounts = [
            self::OTHER => 0
        ];
        if ( count($quantities) > 0 ) {
            foreach ($quantities as $group) {
                $qty = $quantities[$group];
                $discRate = $this->findDiscountRate($group, $qty);
                $discounts[$group->getId()] = $discRate;
            }
            $otherDiscRate = $this->findAccessoryRate($maxQtyGroup, $maxQty);
            $discounts[self::OTHER] = $otherDiscRate;
        }
        return $discounts;
    }

    private function findDiscountRate(DiscountGroup $group, $qty)
    {
        $schedule = $this->findDiscountSchedule($group, $qty);
        if ( $schedule ) {
            return $schedule->getDiscountRate();
        }
        return 0;
    }

    private function findAccessoryRate(DiscountGroup $group, $qty)
    {
        $schedule = $this->findDiscountSchedule($group, $qty);
        if ( $schedule ) {
            return $schedule->getDiscountRateRelated();
        }
        return 0;
    }

    private function findDiscountSchedule(DiscountGroup $group, $qty)
    {
        return $this->rates->findByGroupAndQtyOrdered($group, $qty);
    }

    /**
     * Returns the discount for the given line item as a fraction between
     * 0.0 and 1.0.
     *
     * @param float[] $discounts
     * @param SalesOrderDetail $item
     * @return double
     */
    private function getDiscount(array $discounts, SalesOrderDetail $item)
    {
        $group = $this->groups->findByItem($item);

        if ( ! $group ) {
            return $discounts[self::OTHER];
        }
        $gid = $group->getId();
        return isset($discounts[$gid]) ? $discounts[$gid] : 0.0;
    }
}
