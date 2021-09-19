<?php

namespace Rialto\Ups\Shipping\Webservice;

use DateTime;
use SimpleXMLElement;

class TrackResponse extends UpsXmlResponse
{
    /** @var string */
    private $estimatedDeliveryDate;

    /** @var string */
    private $deliveryDate;

    /**
     * @param string $xml
     */
    protected function parseResults(SimpleXMLElement $xml)
    {
        $this->estimatedDeliveryDate = (string) $xml->Shipment->EstimatedDeliveryDetails->Date;
        $this->deliveryDate = (string) $xml->Shipment->Package->DeliveryDate;
    }

    public function getEstimatedDeliveryDate(): ?DateTime
    {
        return $this->estimatedDeliveryDate ? new DateTime($this->estimatedDeliveryDate) : null;
    }

    public function getDeliveryDate(): ?DateTime
    {
        return $this->deliveryDate ? new DateTime($this->deliveryDate) : null;
    }
}