<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'service_locator.lr3dryr' shared service.

return $this->services['service_locator.lr3dryr'] = new \Symfony\Component\DependencyInjection\ServiceLocator(['commandQueue' => function () {
    $f = function (\Rialto\Port\CommandBus\CommandQueue $v = null) { return $v; }; return $f(${($_ = isset($this->services['Rialto\\Port\\CommandBus\\CommandQueue']) ? $this->services['Rialto\\Port\\CommandBus\\CommandQueue'] : $this->load('getCommandQueueService.php')) && false ?: '_'});
}, 'item' => function () {
    $f = function (\Rialto\Stock\Item\ManufacturedStockItem $v) { return $v; }; return $f(${($_ = isset($this->services['autowired.Rialto\\Stock\\Item\\ManufacturedStockItem']) ? $this->services['autowired.Rialto\\Stock\\Item\\ManufacturedStockItem'] : ($this->services['autowired.Rialto\\Stock\\Item\\ManufacturedStockItem'] = new \Rialto\Stock\Item\ManufacturedStockItem())) && false ?: '_'});
}, 'pcbNgClient' => function () {
    $f = function (\Rialto\PcbNg\Service\PcbNgClient $v = null) { return $v; }; return $f(${($_ = isset($this->services['Rialto\\PcbNg\\Service\\PcbNgClient']) ? $this->services['Rialto\\PcbNg\\Service\\PcbNgClient'] : $this->load('getPcbNgClientService.php')) && false ?: '_'});
}, 'purchasingDataRepository' => function () {
    $f = function (\Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository $v = null) { return $v; }; return $f(${($_ = isset($this->services['Rialto\\Purchasing\\Catalog\\Orm\\PurchasingDataRepository']) ? $this->services['Rialto\\Purchasing\\Catalog\\Orm\\PurchasingDataRepository'] : $this->getPurchasingDataRepositoryService()) && false ?: '_'});
}]);
