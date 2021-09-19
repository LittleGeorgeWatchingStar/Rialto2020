<?php

namespace Rialto\Shipping\Shipment;

use Rialto\Sales\Order\SalesOrderInterface;
use Rialto\Sales\Shipping\ShippableOrder;
use Rialto\Shipping\Method\ShippingMethod;
use Rialto\Shipping\Order\RatableOrder;
use Rialto\Shipping\Shipper\Shipper;

/**
 * Default ShipmentFactory implementation.
 *
 * This implementation is used for shippers that do not have a different
 * implementation registered in the service container. This implementation has
 * no special logic and creates instances of DefaultShipment.
 *
 * @see DefaultShipment
 */
class DefaultShipmentFactory implements ShipmentFactory
{
    public function canUseEdiDocuments(SalesOrderInterface $order)
    {
        return false;
    }

    public function createShipment(ShippableOrder $order, ShippingMethod $method)
    {
        $shipment = new DefaultShipment($order, $method);
        $shipment->setTrackingNumber($order->getTrackingNumber());
        return $shipment;
    }

    public function getShipmentOptions(RatableOrder $order, ?Shipper $shipper = null)
    {
        $shipper = $shipper ?: $order->getShipper();
        $options = [];

        foreach ($shipper->getDefaultShippingMethods() as $method ) {
            $options[] = new BasicShipmentOption($method, 0.0);
        }
        return $options;
    }

    public function ship(SalesOrderShipment $shipment)
    {
        assert($shipment instanceof DefaultShipment );
    }

    public function refreshShippingCosts(SalesOrderShipment $shipment)
    {
        /* nothing to do */
    }
}
