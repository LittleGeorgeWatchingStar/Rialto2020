<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Supplier\Allocation\Web\BinAllocationController' shared autowired service.

$this->services['Rialto\\Supplier\\Allocation\\Web\\BinAllocationController'] = $instance = new \Rialto\Supplier\Allocation\Web\BinAllocationController(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->getDoctrine_Orm_DefaultEntityManagerService()) && false ?: '_'});

$instance->setContainer(${($_ = isset($this->services['service_locator.yscbjmj']) ? $this->services['service_locator.yscbjmj'] : $this->load('getServiceLocator_YscbjmjService.php')) && false ?: '_'}->withContext('Rialto\\Supplier\\Allocation\\Web\\BinAllocationController', $this));

return $instance;
