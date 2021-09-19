<?php

namespace Rialto\Tax;

use Rialto\Sales\Order\SalesOrder;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use TaxJar\Client as TaxJar;

/**
 * A TaxLookup implementation that uses TaxJar's API to find tax rates.
 *
 * @see http://www.taxjar.com
 */
class TaxJarLookup implements TaxLookup
{
    /** @var TaxJar */
    private $api;

    /** @var Facility */
    private $shipFrom;

    public function __construct(TaxJar $api, FacilityRepository $repo)
    {
        $this->api = $api;
        $this->shipFrom = $repo->getHeadquarters();
    }

    /**
     * @return string The name of the company or source providing the tax data.
     */
    public function getProviderName()
    {
        return 'TaxJar';
    }

    /**
     * Updates the tax rates for every line item in the sales order.
     */
    public function updateTaxRates(SalesOrder $order)
    {
        if ($order->isTaxExempt()) {
            $order->setTaxRate(0.0, false);
            return;
        }
        $params = $this->getRequestParams($order);
        $rateInfo = $this->api->taxForOrder($params);
        $rate = $rateInfo->rate;
        $taxShipping = $rateInfo->freight_taxable;
        $order->setTaxRate($rate, $taxShipping);
    }

    private function getRequestParams(SalesOrder $order)
    {
        $to = $order->getShippingAddress();
        $from = $this->shipFrom->getAddress();
        return [
            'amount' => $order->getSubtotalPrice(),
            'shipping' => $order->getShippingPrice(),
            'to_country' => $to->getCountryCode(),
            'to_state' => $to->getStateCode(),
            'to_city' => $to->getCity(),
            'to_street' => $to->getStreet1(),
            'to_zip' => $to->getPostalCode(),
            'from_country' => $from->getCountryCode(),
            'from_state' => $from->getStateCode(),
            'from_city' => $from->getCity(),
            'from_street' => $from->getStreet1(),
            'from_zip' => $from->getPostalCode(),
        ];
    }

}
