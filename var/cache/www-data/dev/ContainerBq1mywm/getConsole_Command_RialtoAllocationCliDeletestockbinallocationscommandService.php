<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'console.command.rialto_allocation_cli_deletestockbinallocationscommand' shared autowired service.

return $this->services['console.command.rialto_allocation_cli_deletestockbinallocationscommand'] = new \Rialto\Allocation\Cli\DeleteStockBinAllocationsCommand(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->getDoctrine_Orm_DefaultEntityManagerService()) && false ?: '_'});
