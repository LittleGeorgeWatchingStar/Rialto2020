<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Sales\SalesLogger' shared autowired service.

return $this->services['Rialto\\Sales\\SalesLogger'] = new \Rialto\Sales\SalesLogger(${($_ = isset($this->services['monolog.logger.sales']) ? $this->services['monolog.logger.sales'] : $this->load('getMonolog_Logger_SalesService.php')) && false ?: '_'});
