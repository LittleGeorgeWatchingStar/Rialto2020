<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Stock\Category\CategoryChange' shared autowired service.

return $this->services['Rialto\\Stock\\Category\\CategoryChange'] = new \Rialto\Stock\Category\CategoryChange(${($_ = isset($this->services['Rialto\\Stock\\Level\\StockLevelService']) ? $this->services['Rialto\\Stock\\Level\\StockLevelService'] : $this->load('getStockLevelServiceService.php')) && false ?: '_'});
