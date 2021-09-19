<?php

namespace Rialto\Shipping\Shipment;

use Rialto\Sales\Shipping\ShippableOrder;

interface SalesOrderShipment extends ShippableOrder, ShipmentOption
{
    /** @return string */
    public function getTrackingNumber();

    /** @return ShipmentPackage[] */
    public function getPackages();

    /** @param ShipmentPackage[] */
    public function setPackages(array $packages);
}
