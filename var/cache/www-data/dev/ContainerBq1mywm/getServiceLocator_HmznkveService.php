<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'service_locator.hmznkve' shared service.

return $this->services['service_locator.hmznkve'] = new \Symfony\Component\DependencyInjection\ServiceLocator(['supplier' => function () {
    $f = function (\Rialto\Purchasing\Supplier\Supplier $v) { return $v; }; return $f(${($_ = isset($this->services['autowired.Rialto\\Purchasing\\Supplier\\Supplier']) ? $this->services['autowired.Rialto\\Purchasing\\Supplier\\Supplier'] : ($this->services['autowired.Rialto\\Purchasing\\Supplier\\Supplier'] = new \Rialto\Purchasing\Supplier\Supplier())) && false ?: '_'});
}]);
