<?php

namespace Rialto\Stock;


/**
 * SKUs that have special logic around them and SKU-related utilities.
 *
 * SKU stands for Stock Keeping Unit.
 */
final class Sku
{
    const PRINTED_LABEL = 'LBL0003';

    /**
     * These items represent service fees and are not physical items.
     */
    const GEPPETTO_FEE = 'KIT90000000';
    const DISCOUNT_GEPPETTO_FEE = 'KIT90000001';
    const NON_RECURRING_ENGINEERING_FEE = 'GSA00003';

    /**
     * When you have a SKU and need an implementation of Item.
     */
    public static function asItem(string $sku): Item
    {
        return new class($sku) implements Item {
            private $sku;

            public function __construct($sku)
            {
                $this->sku = $sku;
            }

            public function getSku()
            {
                return $this->sku;
            }

            public function getStockCode()
            {
                return $this->getSku();
            }
        };
    }

    public static function isServiceFee(string $sku): bool
    {
        return self::isGeppettoFee($sku)
            || $sku === self::NON_RECURRING_ENGINEERING_FEE;
    }

    public static function isGeppettoFee(string $sku): bool
    {
        return in_array($sku, [
            self::GEPPETTO_FEE,
            self::DISCOUNT_GEPPETTO_FEE,
        ]);
    }
}
