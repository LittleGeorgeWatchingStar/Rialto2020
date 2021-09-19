<?php

namespace Rialto\Shipping\Shipment;

use Rialto\Sales\Order\SalesOrderInterface;
use Rialto\Sales\Shipping\ShippableOrder;
use Rialto\Shipping\Method\ShippingMethod;
use Rialto\Shipping\Order\RatableOrder;
use Rialto\Shipping\Shipper\Shipper;

/**
 * Creates shipments and gets shipping options for orders.
 */
interface ShipmentFactory
{
    /**
     * @return SalesOrderShipment
     */
    public function createShipment(ShippableOrder $order, ShippingMethod $method);

    /**
     * @return ShipmentOption[]
     */
    public function getShipmentOptions(RatableOrder $order, ?Shipper $shipper = null);

    /**
     * Ships the given shipment.
     *
     * @param SalesOrderShipment
     */
    public function ship(SalesOrderShipment $shipment);

    /**
     * Updates the shipment by refreshing the shipping costs.
     */
    public function refreshShippingCosts(SalesOrderShipment $shipment);

    /**
     * @return bool
     *  True if the given sales order can use EDI (electronic) export
     *  documents.
     */
    public function canUseEdiDocuments(SalesOrderInterface $order);
}
