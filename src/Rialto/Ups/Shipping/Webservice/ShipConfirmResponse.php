<?php

namespace Rialto\Ups\Shipping\Webservice;

use SimpleXMLElement;

/**
 * Encapsulates and parses the XML ShipConfirm Response from UPS.
 */
class ShipConfirmResponse
extends UpsXmlResponse
{
    /**
     * The UPS shipment digest for a valid shipment.
     *
     * @var string
     */
    private $shipment_digest;

    /**
     * @param string $xml
     */
    protected function parseResults(SimpleXMLElement $xml)
    {
        $this->shipment_digest = (string) $xml->ShipmentDigest;
    }

    /**
     * @return string
     */
    public function getShipmentDigest()
    {
        return $this->shipment_digest;
    }
}
