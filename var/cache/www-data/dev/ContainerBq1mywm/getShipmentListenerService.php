<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Rialto\Magento2\Order\ShipmentListener' shared autowired service.

return $this->services['Rialto\\Magento2\\Order\\ShipmentListener'] = new \Rialto\Magento2\Order\ShipmentListener(${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Magento2\\Api\\Rest\\RestApiFactory']) ? $this->services['Rialto\\Magento2\\Api\\Rest\\RestApiFactory'] : $this->load('getRestApiFactoryService.php')) && false ?: '_'});