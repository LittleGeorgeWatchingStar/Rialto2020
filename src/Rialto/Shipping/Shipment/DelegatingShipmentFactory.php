<?php

namespace Rialto\Shipping\Shipment;

use Doctrine\ORM\EntityManagerInterface;
use Rialto\Sales\Order\SalesOrderInterface;
use Rialto\Sales\Shipping\ShippableOrder;
use Rialto\Shipping\Method\ShippingMethod;
use Rialto\Shipping\Order\RatableOrder;
use Rialto\Shipping\Shipper\Orm\ShipperRepository;
use Rialto\Shipping\Shipper\Shipper;

/**
 * ShipmentFactory implementation that delegates to shipper-specific
 * implementations.
 */
class DelegatingShipmentFactory implements ShipmentFactory
{
    /**
     * The ShipmentFactory to use for Shippers who do not have a specific
     * implementation registered.
     * @var ShipmentFactory
     */
    private $default;

    /** @var ShipmentFactory[] */
    private $impl = [];

    /** @var ShipperRepository */
    private $shipperRepo;

    public function __construct(ShipmentFactory $default,
                                EntityManagerInterface $em)
    {
        $this->default = $default;
        $this->shipperRepo = $em->getRepository(Shipper::class);
    }

    /**
     * Registers a custom ShipmentFactory for the shipper whose name is
     * given.
     */
    public function registerImplementation($shipperName, ShipmentFactory $impl)
    {
        $this->impl[$shipperName] = $impl;
    }

    public function createShipment(ShippableOrder $order, ShippingMethod $method)
    {
        return $this->getImplementationFromOrder($order)->createShipment(
            $order,
            $method
        );
    }

    public function getShipmentOptions(RatableOrder $order, ?Shipper $shipper = null)
    {
        if (!$order->getShipper()) {
            return $this->getAllOptions($order);
        }
        return $this->getImplementationFromOrder($order)->getShipmentOptions($order);
    }

    public function ship(SalesOrderShipment $shipment)
    {
        return $this->getImplementationFromOrder($shipment)->ship($shipment);
    }

    /**
     * @return ShipmentFactory
     *  The shipper-specific implementation of ShipmentFactory
     * @throws \LogicException
     *  If there is no shipper-specific implementation.
     */
    private function getImplementationFromOrder(RatableOrder $order)
    {
        return $this->getImplementation( $order->getShipper() );
    }

    private function getImplementation(Shipper $shipper)
    {
        $name = $shipper->getName();
        if ( isset($this->impl[$name]) ) {
            return $this->impl[$name];
        }
        return $this->default;
    }

    public function refreshShippingCosts(SalesOrderShipment $shipment)
    {
        return $this->getImplementationFromOrder($shipment)->refreshShippingCosts($shipment);
    }

    public function canUseEdiDocuments(SalesOrderInterface $order)
    {
        return $this->getImplementationFromOrder($order)->canUseEdiDocuments($order);
    }

    private function getAllOptions($order): array
    {
        $options = $this->default->getShipmentOptions($order,
            $this->shipperRepo->findHandCarried());
        foreach ($this->impl as $name => $impl) {
            $shipper = $this->shipperRepo->findByName($name);
            $options = array_merge($options, $impl->getShipmentOptions($order, $shipper));
        }

        return $options;
    }
}
