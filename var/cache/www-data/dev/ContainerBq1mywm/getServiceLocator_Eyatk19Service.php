<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'service_locator.eyatk19' shared service.

return $this->services['service_locator.eyatk19'] = new \Symfony\Component\DependencyInjection\ServiceLocator(['factory' => function () {
    $f = function (\Rialto\PcbNg\Service\PickAndPlaceFactory $v = null) { return $v; }; return $f(${($_ = isset($this->services['Rialto\\PcbNg\\Service\\PickAndPlaceFactory']) ? $this->services['Rialto\\PcbNg\\Service\\PickAndPlaceFactory'] : $this->load('getPickAndPlaceFactoryService.php')) && false ?: '_'});
}]);