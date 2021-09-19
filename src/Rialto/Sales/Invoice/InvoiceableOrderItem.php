<?php

namespace Rialto\Sales\Invoice;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Sales\Order\SalesOrderItem;

/**
 * A sales order item that has the methods needed to by invoiced
 * by the accounting system.
 */
interface InvoiceableOrderItem extends SalesOrderItem
{
    /** @return float */
    public function getStandardCost();

    /**
     * Returns the tax rate as a fraction between 0.0 and 1.0.
     * @return float
     */
    public function getTaxRate();

    /**
     * Any adjustments to the base unit price; eg, from Customizations.
     * @return float
     */
    public function getPriceAdjustment();

    /**
     * Returns the discount rate as a fraction between 0.0 and 1.0.
     * @return float
     */
    public function getDiscountRate();

    /** @return GLAccount */
    public function getDiscountAccount();

    /** @return GLAccount */
    public function getStockAccount();

    /** @return GLAccount */
    public function getSalesAccount();
}
