<?php

namespace Rialto\Ups\Shipping\Webservice;

use SimpleXMLElement;

/**
 * Encapsulates and parses the XML Rate Response from UPS.
 */
class RateResponse
extends UpsXmlResponse
{
    /**
     * Information about possible shipping methods.
     *
     * @var array
     */
    private $results = [];

    /**
     * @param string $xml
     */
    protected function parseResults(SimpleXMLElement $xml)
    {
        foreach ( $xml->RatedShipment as $shipment_info ) {
            $result = [];
            $result['regular_cost'] =
                (double) $shipment_info->TotalCharges->MonetaryValue;
            $result['discounted_cost'] = null;
            if ( (bool) $shipment_info->NegotiatedRates ) {
                $result['discounted_cost'] =
                    (double) $shipment_info->NegotiatedRates
                        ->NetSummaryCharges
                        ->GrandTotal
                        ->MonetaryValue;
            }
            $code = (string) $shipment_info->Service->Code;
            $this->results[ $code ] = $result;
        }
   }

    /**
     * Returns a list of valid shipping method codes.
     *
     * @return array
     */
    public function getMethodCodes()
    {
        $codes = array_keys($this->results);
        /* Sort the results by price. */
        usort($codes, [$this, 'compareCost']);
        return $codes;
    }

    /**
     * Returns the regular (non-discounted) cost of the shipping method
     * whose code is given.
     *
     * @param string $code
     * @return double
     */
    public function getRegularCost( $code )
    {
        return $this->results[$code]['regular_cost'];
    }

	/**
     * Returns the discounted cost (if any) of the shipping method
     * whose code is given.
     *
     * @param string $code
     * @return double|null
     */
    public function getDiscountedCost( $code )
    {
        return $this->results[$code]['discounted_cost'];
    }

    /**
     * Compares two results, whose shipping method codes are given, by price.
     *
     * @param string $a
     * @param string $b
     * @return int
     */
    private function compareCost($a, $b)
    {
        $ca = isset($this->results[$a]['discounted_cost'])
            ? $this->results[$a]['discounted_cost']
            : $this->results[$a]['regular_cost'];
        $cb = isset($this->results[$b]['discounted_cost'])
            ? $this->results[$b]['discounted_cost']
            : $this->results[$b]['regular_cost'];
        return $ca > $cb ? 1 : ( $ca == $cb ? 0 : -1 );
    }
}
