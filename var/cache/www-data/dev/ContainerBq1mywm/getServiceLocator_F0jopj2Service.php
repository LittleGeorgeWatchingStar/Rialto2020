<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'service_locator.f0jopj2' shared service.

return $this->services['service_locator.f0jopj2'] = new \Symfony\Component\DependencyInjection\ServiceLocator(['stockItemDeleteService' => function () {
    $f = function (\Rialto\Stock\Item\StockItemDeleteService $v = null) { return $v; }; return $f(${($_ = isset($this->services['Rialto\\Stock\\Item\\StockItemDeleteService']) ? $this->services['Rialto\\Stock\\Item\\StockItemDeleteService'] : $this->load('getStockItemDeleteServiceService.php')) && false ?: '_'});
}]);
