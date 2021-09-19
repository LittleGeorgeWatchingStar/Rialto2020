<?php

namespace Rialto\Shipping\Method\ShippingTimeEstimator;

use DateInterval;
use DateTime;
use Rialto\Shipping\Method\ShippingMethod;

/**
 * TODO: Handle delivery time or use courier API.
 */
class ShippingTimeEstimator implements ShippingTimeEstimatorInterface
{
    private $shippingEstimates;

    public function __construct()
    {
        /**
         * In days.
         */
        $this->shippingEstimates = [
            'UPS' => [
                'default' => 1,
                '01' => 1, // Next business day by 10:30 AM OR 12:00 PM.
                '02' => 2, // 2nd business days by 10:30 AM OR 12:00 PM.
                '03' => 5, // 1-5 business days (varies based on location to and from).
                '07' => 3, // 1-3 business days by 10:30 AM OR 12:00 PM.
                '08' => 5, // 2-5 business days by end of day.
                '11' => 5, // I just made this one up...
                '12' => 3, // 3 business days by end of day.
                '13' => 3, // Next business day by 3:00 PM OR 4:30 PM.
                '14' => 3, // Next business day by 8:00 AM OR 9:00 AM.
                '54' => 3, // 2-3 business days by 8:30 AM OR 9:00 AM.
                '59' => 2, // 2nd business days by 8:30 AM OR 9:00 AM.
                '65' => 3, // 1-3 business days by end of day.

            ],
            'Hand-carried' => [
                'default' => 1,
                'HAND' => 1,
            ],
            'DHL' => [
                'default' => 1,
                '00' => 5 // I just made this one up too...
            ],
        ];
    }

    /**
     * @return DateTime|null
     */
    public function getEstimatedDeliveryDate(ShippingMethod $shippingMethod,
                                             DateTime $dateShipped)
    {
        $shipper = $shippingMethod->getShipper();

        if (!$this->hasShippingEstimate($shippingMethod)) {
            return null;
        }

        /** @var int $days */
        $days = $this->shippingEstimates[$shipper->getName()][$shippingMethod->getCode()];

        $deliveryDate = clone $dateShipped;
        while ($days > 0) {
            $deliveryDate->modify('+1 day');
            if ($this->isBusinessDay($deliveryDate)) {
                $days = $days - 1;
            }
        }

        return $deliveryDate;
    }

    private function hasShippingEstimate(ShippingMethod $shippingMethod): bool
    {
        $shipper = $shippingMethod->getShipper();
        return array_key_exists($shipper->getName(), $this->shippingEstimates) &&
            array_key_exists($shippingMethod->getCode(), $this->shippingEstimates[$shipper->getName()]);
    }

    private function isBusinessDay(DateTime $date): bool
    {
        $dayOfTheWeek = (int)$date->format('N');
        return $dayOfTheWeek <= 5;
    }
}