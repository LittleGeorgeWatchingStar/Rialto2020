<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Rialto\Stock\Bin\StockBinVoter' shared autowired service.

return $this->services['Rialto\\Stock\\Bin\\StockBinVoter'] = new \Rialto\Stock\Bin\StockBinVoter(${($_ = isset($this->services['security.role_hierarchy']) ? $this->services['security.role_hierarchy'] : $this->load('getSecurity_RoleHierarchyService.php')) && false ?: '_'}, ${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'});