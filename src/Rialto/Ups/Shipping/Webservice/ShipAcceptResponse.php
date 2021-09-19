<?php

namespace Rialto\Ups\Shipping\Webservice;

use SimpleXMLElement;

/**
 * Encapsulates and parses the XML ShipAccept Response from UPS.
 */
class ShipAcceptResponse
extends UpsXmlResponse
{
    private $discountedCost = null;
    private $regularCost = null;
    private $trackingNumber = null;
    private $shippingLabels = [];
    private $exportDocuments = null;

    /**
     * @param string $xml
     */
    protected function parseResults(SimpleXMLElement $xml)
    {
        /* tracking number */
        $this->trackingNumber =
            (string) $xml->ShipmentResults->ShipmentIdentificationNumber;

        /* shipping cost */
        $this->regularCost = (double) $xml->ShipmentResults
                ->ShipmentCharges
                ->TotalCharges
                ->MonetaryValue;

        /* See if we've got discounted rates */
        if ( (bool) $xml->ShipmentResults->NegotiatedRates ) {
            $this->discountedCost = (double) $xml->ShipmentResults
                ->NegotiatedRates
                ->NetSummaryCharges
                ->GrandTotal
                ->MonetaryValue;
        }

        /* Shipping labels */
        foreach ( $xml->ShipmentResults->PackageResults as $PackageResult ) {
            $trackingNumber = (string) $PackageResult->TrackingNumber;
            $encodedImage = (string) $PackageResult->LabelImage->GraphicImage;
            $this->shippingLabels[$trackingNumber] = base64_decode($encodedImage);
        }

        /* Export documents */
        if ( (bool) $xml->ShipmentResults->Form ) {
            $encodedFile = (string) $xml->ShipmentResults->Form->Image->GraphicImage;
            $this->exportDocuments = base64_decode($encodedFile);
        }
    }

    /**
     * Returns the discounted shipping cost, or null if there is no discount.
     *
     * @return double|null
     */
    public function getDiscountedCost()
    {
        return $this->discountedCost;
    }

    /**
     * @return string
     */
    public function getTrackingNumber()
    {
        return $this->trackingNumber;
    }

    /**
     * @return double
     */
    public function getRegularCost()
    {
        return $this->regularCost;
    }

    /**
     * @return string[]
     */
    public function getShippingLabels()
    {
        return $this->shippingLabels;
    }

    /** @return string */
    public function getExportDocuments()
    {
        return $this->exportDocuments;
    }
}
