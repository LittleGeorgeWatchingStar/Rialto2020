<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Rialto\Supplier\Logger' shared autowired service.

return $this->services['Rialto\\Supplier\\Logger'] = new \Rialto\Supplier\Logger(${($_ = isset($this->services['monolog.logger.supplier']) ? $this->services['monolog.logger.supplier'] : $this->load('getMonolog_Logger_SupplierService.php')) && false ?: '_'});
