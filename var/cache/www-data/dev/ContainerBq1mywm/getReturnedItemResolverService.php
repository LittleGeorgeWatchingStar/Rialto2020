<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Stock\Returns\Problem\ReturnedItemResolver' shared autowired service.

return $this->services['Rialto\\Stock\\Returns\\Problem\\ReturnedItemResolver'] = new \Rialto\Stock\Returns\Problem\ReturnedItemResolver(${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Stock\\Transfer\\TransferReceiver']) ? $this->services['Rialto\\Stock\\Transfer\\TransferReceiver'] : $this->load('getTransferReceiverService.php')) && false ?: '_'}, ${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->getDebug_EventDispatcherService()) && false ?: '_'}, ${($_ = isset($this->services['monolog.logger.receiving']) ? $this->services['monolog.logger.receiving'] : $this->load('getMonolog_Logger_ReceivingService.php')) && false ?: '_'});
