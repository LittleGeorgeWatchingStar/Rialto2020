<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'console.command.rialto_magento2_stock_cli_syncstocklevelscommand' shared autowired service.

return $this->services['console.command.rialto_magento2_stock_cli_syncstocklevelscommand'] = new \Rialto\Magento2\Stock\Cli\SyncStockLevelsCommand(${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Magento2\\Api\\Rest\\RestApiFactory']) ? $this->services['Rialto\\Magento2\\Api\\Rest\\RestApiFactory'] : $this->load('getRestApiFactoryService.php')) && false ?: '_'});