<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Gumstix\RestBundle\Handler\AccessDeniedHandler' shared service.

return $this->services['Gumstix\\RestBundle\\Handler\\AccessDeniedHandler'] = new \Gumstix\RestBundle\Handler\AccessDeniedHandler(${($_ = isset($this->services['twig.controller.exception']) ? $this->services['twig.controller.exception'] : $this->load('getTwig_Controller_ExceptionService.php')) && false ?: '_'});