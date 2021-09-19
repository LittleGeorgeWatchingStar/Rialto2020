<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Rialto\Manufacturing\PurchaseOrder\Command\OrderPartsHandler' shared autowired service.

return $this->services['Rialto\\Manufacturing\\PurchaseOrder\\Command\\OrderPartsHandler'] = new \Rialto\Manufacturing\PurchaseOrder\Command\OrderPartsHandler(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->getDoctrine_Orm_DefaultEntityManagerService()) && false ?: '_'}, ${($_ = isset($this->services['logger']) ? $this->services['logger'] : $this->load('getLogger2Service.php')) && false ?: '_'}, ${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'}, ${($_ = isset($this->services['validator']) ? $this->services['validator'] : $this->getValidatorService()) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Purchasing\\Producer\\StockProducerFactory']) ? $this->services['Rialto\\Purchasing\\Producer\\StockProducerFactory'] : $this->load('getStockProducerFactoryService.php')) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Allocation\\Allocation\\AllocationFactory']) ? $this->services['Rialto\\Allocation\\Allocation\\AllocationFactory'] : ($this->services['Rialto\\Allocation\\Allocation\\AllocationFactory'] = new \Rialto\Allocation\Allocation\AllocationFactory())) && false ?: '_'});
