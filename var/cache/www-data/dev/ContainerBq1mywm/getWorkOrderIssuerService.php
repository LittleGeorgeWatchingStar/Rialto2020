<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Manufacturing\WorkOrder\Issue\WorkOrderIssuer' shared autowired service.

return $this->services['Rialto\\Manufacturing\\WorkOrder\\Issue\\WorkOrderIssuer'] = new \Rialto\Manufacturing\WorkOrder\Issue\WorkOrderIssuer(${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'});