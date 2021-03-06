<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Geppetto\Design\Web\DesignController' shared autowired service.

$a = ${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->getDoctrine_Orm_DefaultEntityManagerService()) && false ?: '_'};

return $this->services['Rialto\\Geppetto\\Design\\Web\\DesignController'] = new \Rialto\Geppetto\Design\Web\DesignController(${($_ = isset($this->services['Rialto\\Geppetto\\Design\\DesignFactory']) ? $this->services['Rialto\\Geppetto\\Design\\DesignFactory'] : $this->load('getDesignFactoryService.php')) && false ?: '_'}, new \Rialto\Geppetto\Design\DesignStockItemFactory($a, ${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'}, new \Rialto\Geppetto\Design\DesignStockItemTemplateFactory($a), ${($_ = isset($this->services['Rialto\\Stock\\Item\\StockItemFactory']) ? $this->services['Rialto\\Stock\\Item\\StockItemFactory'] : $this->load('getStockItemFactoryService.php')) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Stock\\Cost\\StandardCostUpdater']) ? $this->services['Rialto\\Stock\\Cost\\StandardCostUpdater'] : $this->load('getStandardCostUpdaterService.php')) && false ?: '_'}, ${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->getDebug_EventDispatcherService()) && false ?: '_'}), $a, ${($_ = isset($this->services['Rialto\\Madison\\MadisonClient']) ? $this->services['Rialto\\Madison\\MadisonClient'] : $this->load('getMadisonClientService.php')) && false ?: '_'});
