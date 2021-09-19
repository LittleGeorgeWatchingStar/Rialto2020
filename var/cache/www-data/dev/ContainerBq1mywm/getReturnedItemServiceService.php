<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Stock\Returns\ReturnedItemService' shared autowired service.

return $this->services['Rialto\\Stock\\Returns\\ReturnedItemService'] = new \Rialto\Stock\Returns\ReturnedItemService(${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Stock\\Returns\\Problem\\ReturnedItemResolver']) ? $this->services['Rialto\\Stock\\Returns\\Problem\\ReturnedItemResolver'] : $this->load('getReturnedItemResolverService.php')) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Stock\\Transfer\\TransferService']) ? $this->services['Rialto\\Stock\\Transfer\\TransferService'] : $this->load('getTransferServiceService.php')) && false ?: '_'});
