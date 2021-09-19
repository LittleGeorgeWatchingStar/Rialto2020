<?php

namespace Rialto\Ups\Shipping;

use Rialto\Sales\Order\SalesOrderInterface;
use Rialto\Sales\Shipping\ShippableOrder;
use Rialto\Shipping\Method\ShippingMethod;
use Rialto\Shipping\Order\RatableOrder;
use Rialto\Shipping\Shipment\SalesOrderShipment;
use Rialto\Shipping\Shipment\ShipmentFactory;
use Rialto\Shipping\Shipper\Shipper;
use Rialto\Ups\Shipping\Export\UpsEdiCountries;
use Rialto\Ups\Shipping\Webservice\ShipAcceptResponse;
use Rialto\Ups\Shipping\Webservice\UpsApiService;


/**
 * ShipmentFactory implementation that creates UPS shipments using UPS web
 * services.
 */
class UpsShipmentFactory implements ShipmentFactory
{
    /** @var UpsApiService */
    private $apiService;

    public function __construct(UpsApiService $apiService)
    {
        $this->apiService = $apiService;
    }

    public function createShipment(ShippableOrder $order, ShippingMethod $method)
    {
        return new UpsShipment($order, $method);
    }

    public function getShipmentOptions(RatableOrder $order, ?Shipper $shipper = null)
    {
        $shipper = $shipper ?: $order->getShipper();
        $response = $this->apiService->Shop($order, $shipper->getAccountNumber());

        $results = [];
        foreach ($response->getMethodCodes() as $code) {
            $method = $shipper->getShippingMethod($code);
            $option = new UpsShipmentOption($method);
            $option->setRegularCost(
                $response->getRegularCost($code)
            );
            $option->setDiscountedCost(
                $response->getDiscountedCost($code)
            );
            $results[] = $option;
        }
        return $results;
    }

    /**
     * @param UpsShipment $shipment
     */
    public function ship(SalesOrderShipment $shipment)
    {
        assertion($shipment instanceof UpsShipment);
        $digest = $this->confirm($shipment);
        $acceptResponse = $this->accept($digest);
        $shipment->acceptResponse($acceptResponse);
        return $shipment;
    }

    /**
     * Executes the ShipConfirm request to the UPS shipping system.  This
     * process generates a shipment digest, which can then be used to request
     * acceptance by the UPS system.
     *
     * @return string The shipment digest
     */
    private function confirm(UpsShipment $shipment)
    {
        $response = $this->apiService->shipConfirm($shipment);
        return $response->getShipmentDigest();
    }

    /**
     * Executes the ShipAccept request to the UPS shipping system.  Note that
     * this shipment must be confirmed before this method can be called.
     *
     * @return ShipAcceptResponse
     */
    private function accept($digest)
    {
        return $this->apiService->shipAccept($digest);
    }

    public function refreshShippingCosts(SalesOrderShipment $shipment)
    {
        assertion($shipment instanceof UpsShipment);
        /* @var $shipment UpsShipment */

        $response = $this->apiService->rate($shipment);
        $method = $shipment->getShippingMethod();

        $shipment->setRegularCost($response->getRegularCost($method->getCode()));
        $shipment->setDiscountedCost($response->getDiscountedCost($method->getCode()));
    }

    public function canUseEdiDocuments(SalesOrderInterface $order)
    {
        $deliveryAddress = $order->getDeliveryAddress();
        return UpsEdiCountries::isInList($deliveryAddress->getCountryCode());
    }
}
