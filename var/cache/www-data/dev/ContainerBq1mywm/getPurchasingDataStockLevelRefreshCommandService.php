<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Purchasing\Catalog\Cli\PurchasingDataStockLevelRefreshCommand' shared autowired service.

return $this->services['Rialto\\Purchasing\\Catalog\\Cli\\PurchasingDataStockLevelRefreshCommand'] = new \Rialto\Purchasing\Catalog\Cli\PurchasingDataStockLevelRefreshCommand(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->getDoctrine_Orm_DefaultEntityManagerService()) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Port\\CommandBus\\CommandQueue']) ? $this->services['Rialto\\Port\\CommandBus\\CommandQueue'] : $this->load('getCommandQueueService.php')) && false ?: '_'});
