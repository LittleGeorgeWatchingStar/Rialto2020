<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'monolog.handler.flash' shared autowired service.

$this->services['monolog.handler.flash'] = $instance = new \Rialto\Logging\FlashHandler();

$instance->setSession(${($_ = isset($this->services['session']) ? $this->services['session'] : $this->load('getSessionService.php')) && false ?: '_'});

return $instance;
