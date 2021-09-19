<?php

namespace Rialto\Tax;

use Rialto\Sales\Order\SalesOrder;

/**
 * Service for looking up sales tax rates.
 */
interface TaxLookup
{
    /**
     * Updates the tax rates for every line item in the sales order.
     */
    public function updateTaxRates(SalesOrder $order);

    /**
     * @return string The name of the company or source providing the tax data.
     */
    public function getProviderName();
}
