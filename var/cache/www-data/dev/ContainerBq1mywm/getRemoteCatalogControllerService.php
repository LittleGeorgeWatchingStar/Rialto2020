<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Purchasing\Catalog\Remote\Web\RemoteCatalogController' shared autowired service.

return $this->services['Rialto\\Purchasing\\Catalog\\Remote\\Web\\RemoteCatalogController'] = new \Rialto\Purchasing\Catalog\Remote\Web\RemoteCatalogController(${($_ = isset($this->services['Rialto\\Stock\\Web\\StockRouter']) ? $this->services['Rialto\\Stock\\Web\\StockRouter'] : $this->getStockRouterService()) && false ?: '_'});
