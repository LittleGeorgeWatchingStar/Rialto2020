<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'service_locator.jwvgbsz' shared service.

return $this->services['service_locator.jwvgbsz'] = new \Symfony\Component\DependencyInjection\ServiceLocator(['printer' => function () {
    $f = function (\Rialto\Manufacturing\WorkType\ProductLabelPrinter $v = null) { return $v; }; return $f(${($_ = isset($this->services['Rialto\\Manufacturing\\WorkType\\ProductLabelPrinter']) ? $this->services['Rialto\\Manufacturing\\WorkType\\ProductLabelPrinter'] : $this->load('getProductLabelPrinterService.php')) && false ?: '_'});
}]);
