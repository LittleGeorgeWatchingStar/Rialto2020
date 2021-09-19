<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'monolog.logger.automation' shared service.

$this->services['monolog.logger.automation'] = $instance = new \Symfony\Bridge\Monolog\Logger('automation');

$instance->pushHandler(${($_ = isset($this->services['monolog.handler.console']) ? $this->services['monolog.handler.console'] : $this->getMonolog_Handler_ConsoleService()) && false ?: '_'});
$instance->pushHandler(${($_ = isset($this->services['monolog.handler.automation']) ? $this->services['monolog.handler.automation'] : $this->load('getMonolog_Handler_AutomationService.php')) && false ?: '_'});
$instance->pushHandler(${($_ = isset($this->services['monolog.handler.sentry']) ? $this->services['monolog.handler.sentry'] : $this->getMonolog_Handler_SentryService()) && false ?: '_'});

return $instance;