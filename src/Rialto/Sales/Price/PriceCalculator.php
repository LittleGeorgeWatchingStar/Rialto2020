<?php

namespace Rialto\Sales\Price;

use Rialto\Accounting\Money;
use Rialto\Sales\Invoice\InvoiceableOrderItem;
use Rialto\Sales\Invoice\SalesInvoiceItem;
use Rialto\Sales\Order\TaxableOrder;

/**
 * Calculates prices for sales orders, invoices, etc.
 *
 * To avoid rounding errors, the logic for calculating prices on these
 * types of things is more complicated that you might think, which is
 * why this class is necessary.
 */
class PriceCalculator
{
    const DEFAULT_PRECISION = 2;

    private $precision;

    public function __construct($precision = self::DEFAULT_PRECISION)
    {
        $this->precision = $precision;
    }

    /**
     * @return float
     */
    public function calculateFinalUnitPrice(InvoiceableOrderItem $item)
    {
        $unrounded = ($item->getBaseUnitPrice() + $item->getPriceAdjustment() )
            * (1.0 - $item->getDiscountRate());
        return $this->round($unrounded);
    }

    private function round($amount)
    {
        return Money::round($amount, $this->precision);
    }

    /**
     * @return float
     */
    public function calculateTaxAmount(TaxableOrder $order, $shippingTaxRate = 0)
    {
        $totals = [];
        foreach ( $order->getLineItems() as $item ) {
            $rate = (string) $item->getTaxRate();
            if (! isset($totals[$rate])) {
                $totals[$rate] = 0;
            }
            $totals[$rate] += $item->getExtendedPrice();
        }
        $shipTax = (string) $shippingTaxRate;
        if (! isset($totals[$shipTax])) {
            $totals[$shipTax] = 0;
        }
        $totals[$shipTax] += $order->getShippingPrice();

        $total = 0;
        foreach ( $totals as $rate => $extPrice ) {
            $total += ((float) $rate * $extPrice);
        }

        return $this->round($total);
    }

    /**
     * @param SalesInvoiceItem[] $items
     * @param float $shippingPrice
     * @param int $shippingTaxRate
     * @return float
     */
    public function calculateItemsTaxAmount($items, $shippingPrice, $shippingTaxRate = 0)
    {
        $totals = [];
        foreach ( $items as $item ) {
            $rate = (string) $item->getTaxRate();
            if (! isset($totals[$rate])) {
                $totals[$rate] = 0;
            }
            $totals[$rate] += $item->getExtendedPrice();
        }
        $shipTax = (string) $shippingTaxRate;
        if (! isset($totals[$shipTax])) {
            $totals[$shipTax] = 0;
        }
        $totals[$shipTax] += $shippingPrice;

        $total = 0;
        foreach ( $totals as $rate => $extPrice ) {
            $total += ((float) $rate * $extPrice);
        }

        return $this->round($total);
    }

}
