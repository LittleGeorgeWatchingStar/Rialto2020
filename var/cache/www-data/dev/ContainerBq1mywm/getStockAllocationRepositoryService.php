<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Manufacturing\Allocation\Orm\StockAllocationRepository' shared autowired service.

return $this->services['Rialto\\Manufacturing\\Allocation\\Orm\\StockAllocationRepository'] = new \Rialto\Manufacturing\Allocation\Orm\DQL\DqlStockAllocationRepository(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->getDoctrine_Orm_DefaultEntityManagerService()) && false ?: '_'});
