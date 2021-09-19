<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Sales\Invoice\SalesInvoiceProcessor' shared autowired service.

return $this->services['Rialto\\Sales\\Invoice\\SalesInvoiceProcessor'] = new \Rialto\Sales\Invoice\SalesInvoiceProcessor(${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Shipping\\Shipment\\ShipmentFactory']) ? $this->services['Rialto\\Shipping\\Shipment\\ShipmentFactory'] : $this->load('getShipmentFactoryService.php')) && false ?: '_'}, ${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->getDebug_EventDispatcherService()) && false ?: '_'}, ${($_ = isset($this->services['monolog.logger.flash']) ? $this->services['monolog.logger.flash'] : $this->load('getMonolog_Logger_FlashService.php')) && false ?: '_'});