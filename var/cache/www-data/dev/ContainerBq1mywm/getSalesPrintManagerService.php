<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Sales\SalesPrintManager' shared autowired service.

return $this->services['Rialto\\Sales\\SalesPrintManager'] = new \Rialto\Sales\SalesPrintManager(${($_ = isset($this->services['Rialto\\Sales\\SalesPdfGenerator']) ? $this->services['Rialto\\Sales\\SalesPdfGenerator'] : $this->load('getSalesPdfGeneratorService.php')) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Printing\\Job\\PrintQueue']) ? $this->services['Rialto\\Printing\\Job\\PrintQueue'] : $this->load('getPrintQueueService.php')) && false ?: '_'}, 'standard');