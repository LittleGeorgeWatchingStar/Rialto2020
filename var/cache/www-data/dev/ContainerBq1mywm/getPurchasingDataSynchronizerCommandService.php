<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Purchasing\Catalog\Cli\PurchasingDataSynchronizerCommand' shared autowired service.

return $this->services['Rialto\\Purchasing\\Catalog\\Cli\\PurchasingDataSynchronizerCommand'] = new \Rialto\Purchasing\Catalog\Cli\PurchasingDataSynchronizerCommand(${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Purchasing\\Catalog\\PurchasingDataSynchronizer']) ? $this->services['Rialto\\Purchasing\\Catalog\\PurchasingDataSynchronizer'] : $this->load('getPurchasingDataSynchronizerService.php')) && false ?: '_'});