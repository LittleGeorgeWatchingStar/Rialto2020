<?php

namespace Rialto\Shipping\Export\Document;

use Gumstix\GeographyBundle\Model\PostalAddress;
use Rialto\Sales\Shipping\ShippableOrder;
use Rialto\Sales\Shipping\ShippableOrderItem;
use Rialto\Stock\Item\Eccn;

/**
 * The Electronic Export Information must be filed for any international
 * shipment containing a line item whose value exceeds a certain amount
 * (currently $2500).
 *
 * Formerly called the Shippers' Export Declaration (SED).
 */
class ElectronicExportInformation
{
    /**
     * If any line item in an international order exceeds this price, then an
     * EEI form is required.
     *
     * @var int
     */
    const THRESHOLD_PRICE = 2500; // dollars

    const SED_FILING_OPTION = '01';
    const BOND_CODE_NOT_IN_BOND = '70';
    const EXCEPTION_CODE_NO_LICENSE_REQUIRED = 'NLR';
    const EXCEPTION_CODE_ENCRYPTED_SOFTWARE = 'ENC';
    const POINT_OF_ORIGIN_CALIFORNIA = 'CA';
    const PARTIES_TO_TRANS_NOT_RELATED = 'N';
    const MODE_OF_TRANSPORT_AIR = 'Air';

    const EXPORT_TYPE_DOMESTIC = 'D';
    const EXPORT_TYPE_FOREIGN = 'F';

    /** @var ShippableOrder */
    private $order;

    private $trackingNumber = null;

    public function __construct(ShippableOrder $order)
    {
        $this->order = $order;
    }

    public function __toString()
    {
        return "EEI document for {$this->order}";
    }

    public function isRequired()
    {
        if ($this->shipmentIsTo('US')) {
            return false;
        }
        if ($this->shipmentIsTo('CA')) {
            return false;
        }

        /* Foreign shipments require it if there is a sufficiently expensive
         * line item. */
        foreach ($this->order->getLineItems() as $item) {
            if ($this->lineItemRequiresEEI($item)) {
                return true;
            }
        }
        return false;
    }

    private function shipmentIsTo($countryCode)
    {
        $address = $this->order->getDeliveryAddress();
        return $address->getCountryCode() == $countryCode;
    }


    private function lineItemRequiresEEI(ShippableOrderItem $item)
    {
        return $item->getExtendedValue() > self::THRESHOLD_PRICE;
    }

    public function setTrackingNumber($tracking)
    {
        $this->trackingNumber = $tracking;
        return $this;
    }

    public function getTrackingNumber()
    {
        return $this->trackingNumber;
    }

    public function getFilingOption()
    {
        return self::SED_FILING_OPTION;
    }

    public function getDeliveryCompany()
    {
        return $this->order->getDeliveryCompany();
    }

    public function getDeliveryName()
    {
        return $this->order->getDeliveryName();
    }

    /** @return PostalAddress */
    public function getDeliveryAddress()
    {
        return $this->order->getDeliveryAddress();
    }

    /**
     * Returns only those items that need to be listed on the EEI form.
     *
     * @return ShippableOrderItem[]
     */
    public function getEeiLineItems()
    {
        $sedItems = [];
        foreach ($this->order->getLineItems() as $item) {
            if ($this->lineItemRequiresEEI($item)) {
                $sedItems[] = $item;
            }
        }
        return $sedItems;
    }

    public function getBondCode()
    {
        return self::BOND_CODE_NOT_IN_BOND;
    }

    public function getExportTypeCode(ShippableOrderItem $item)
    {
        return ($item->getCountryOfOrigin() == 'US')
            ? self::EXPORT_TYPE_DOMESTIC
            : self::EXPORT_TYPE_FOREIGN;
    }

    public function getPointOfOrigin()
    {
        return self::POINT_OF_ORIGIN_CALIFORNIA;
    }

    public function getModeOfTransport()
    {
        return self::MODE_OF_TRANSPORT_AIR;
    }

    public function getPartiesToTransaction()
    {
        return self::PARTIES_TO_TRANS_NOT_RELATED;
    }

    public function getLicenseExceptionCode()
    {
        foreach ($this->order->getLineItems() as $item) {
            if ($this->itemRequiresExportLicense($item)) {
                return $this->getExportLicense($item);
            }
        }
        return self::EXCEPTION_CODE_NO_LICENSE_REQUIRED;
    }

    private function itemRequiresExportLicense(ShippableOrderItem $item)
    {
        return $this->getExportLicense($item) !=
        self::EXCEPTION_CODE_NO_LICENSE_REQUIRED;
    }

    private function getExportLicense(ShippableOrderItem $item)
    {
        switch ($item->getEccnCode()) {
            case Eccn::INFOSEC:
                return self::EXCEPTION_CODE_ENCRYPTED_SOFTWARE;
            default:
                return self::EXCEPTION_CODE_NO_LICENSE_REQUIRED;
        }
    }

    public function requiresExportLicense()
    {
        return $this->getLicenseExceptionCode() !=
        self::EXCEPTION_CODE_NO_LICENSE_REQUIRED;
    }

    public function getEccnCode()
    {
        foreach ($this->order->getLineItems() as $item) {
            if ($this->itemRequiresExportLicense($item)) {
                return $item->getEccnCode();
            }
        }
        return self::EXCEPTION_CODE_NO_LICENSE_REQUIRED;
    }

    public function getContactPhone()
    {
        return $this->order->getContactPhone();
    }
}
