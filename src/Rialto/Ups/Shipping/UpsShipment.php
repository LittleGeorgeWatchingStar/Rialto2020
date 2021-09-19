<?php

namespace Rialto\Ups\Shipping;

use Rialto\Sales\Shipping\ShippableOrder;
use Rialto\Shipping\Method\ShippingMethod;
use Rialto\Shipping\Order\RatableOrder;
use Rialto\Shipping\Shipment\SalesOrderShipment;
use Rialto\Shipping\Shipment\ShipmentPackage;
use Rialto\Ups\Shipping\Webservice\ShipAcceptResponse;


class UpsShipment implements SalesOrderShipment
{
    private $order;
    private $method;
    private $packages = [];
    private $trackingNumber;
    private $regularCost;
    private $discountedCost;
    private $shippingLabels = [];
    private $exportDocuments;

    public function __construct(
        ShippableOrder $order,
        ShippingMethod $method = null)
    {
        $this->order = $order;
        $this->method = $method;
        $this->packages = self::createPackages($order);
    }

    /**
     * @param RatableOrder $order
     * @return ShipmentPackage[]
     */
    public static function createPackages(RatableOrder $order)
    {
        /* Weights less than 0.1 are not allowed by UPS. */
        $totalWeight = max($order->getTotalWeight(), 0.1);
        return self::createPackagesOfEqualWeight($totalWeight);
    }

    private static function createPackagesOfEqualWeight($totalWeight)
    {
        $packages = [];
        $numPackagesRequired = ceil($totalWeight / ShipmentPackage::MAX_WEIGHT);
        $packageWeight = $totalWeight / $numPackagesRequired;
        for ($i = 0; $i < $numPackagesRequired; $i++) {
            $packages[] = new ShipmentPackage($packageWeight);
        }
        return $packages;
    }

    public function getDeliveryCompany()
    {
        return $this->order->getDeliveryCompany();
    }

    public function getDeliveryName()
    {
        return $this->order->getDeliveryName();
    }

    public function getDeliveryAddress()
    {
        return $this->order->getDeliveryAddress();
    }

    public function getContactPhone()
    {
        return $this->order->getContactPhone();
    }

    public function getComments()
    {
        return $this->order->getComments();
    }

    public function getLineItems()
    {
        return $this->order->getLineItems();
    }

    public function getTangibleLineItems()
    {
        return $this->order->getTangibleLineItems();
    }

    /**
     * Returns the discounted shipping cost, or null if there is no
     * discount available.
     *
     * @see getShippingCost()
     * @return double|null
     */
    public function getDiscountedCost()
    {
        return $this->discountedCost;
    }

    /**
     * Sets the discounted cost of this shipment.  Note that this cost can be
     * set other ways; for example, by calling accept().
     *
     * @see setRegularCost()
     * @param double $cost
     */
    public function setDiscountedCost($cost)
    {
        $this->discountedCost = $cost;
    }


    /**
     * Returns the non-discounted cost of this shipment.
     *
     * @return double
     */
    public function getRegularCost()
    {
        return $this->regularCost;
    }

    /**
     * Sets the (non-discounted) cost of this shipment.  Note that this
     * cost can be set other ways; for example, by calling accept().
     *
     * @see setDiscountedCost()
     * @param double $cost
     */
    public function setRegularCost($cost)
    {
        $this->regularCost = $cost;
    }

    public function getOrderNumber()
    {
        return $this->order->getOrderNumber();
    }

    public function getCustomerReference(): string
    {
        return $this->order->getCustomerReference();
    }

    /**
     * Returns the discounted shipping cost if there is one, or the regular
     * cost otherwise.
     *
     * @see SalesOrderShipment::getShippingCost()
     * @see getDiscountedCost()
     * @see getRegularCost()
     */
    public function getShippingCost()
    {
        $discounted = $this->getDiscountedCost();
        if (null === $discounted) {
            return $this->getRegularCost();
        }
        return $discounted;
    }

    public function getShippingPrice()
    {
        return $this->order->getShippingPrice();
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

    /**
     * @see SalesOrderShipment::getShippingMethod()
     * @return ShippingMethod
     */
    public function getShippingMethod()
    {
        return $this->method;
    }

    public function getCode()
    {
        return $this->method->getCode();
    }

    public function getName()
    {
        return $this->method->getName();
    }

    public function getSubtotalPrice()
    {
        return $this->order->getSubtotalPrice();
    }

    public function getSubtotalValue()
    {
        return $this->order->getSubtotalValue();
    }

    public function getReasonForShipping()
    {
        return $this->order->getReasonForShipping();
    }

    public function getTrackingNumber()
    {
        return $this->trackingNumber;
    }

    public function acceptResponse(ShipAcceptResponse $response)
    {
        $this->trackingNumber = $response->getTrackingNumber();
        $this->regularCost = $response->getRegularCost();
        $this->discountedCost = $response->getDiscountedCost();
        $this->shippingLabels = $response->getShippingLabels();
        $this->exportDocuments = $response->getExportDocuments();
    }

    public function getTotalWeight()
    {
        $total = 0;
        foreach ($this->packages as $pkg) {
            $total += $pkg->getWeight();
        }
        return $total;
    }

    public function getPackages()
    {
        return $this->packages;
    }

    public function setPackages(array $packages)
    {
        $this->packages = $packages;
    }

    public function getShipper()
    {
        return $this->order->getShipper();
    }

    public function shipperPaysDuties()
    {
        return $this->order->shipperPaysDuties();
    }
}
