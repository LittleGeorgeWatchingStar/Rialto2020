<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'web_profiler.controller.router' shared service.

return $this->services['web_profiler.controller.router'] = new \Symfony\Bundle\WebProfilerBundle\Controller\RouterController(NULL, ${($_ = isset($this->services['twig']) ? $this->services['twig'] : $this->getTwigService()) && false ?: '_'}, ${($_ = isset($this->services['Symfony\\Component\\Routing\\RouterInterface']) ? $this->services['Symfony\\Component\\Routing\\RouterInterface'] : $this->getRouterInterfaceService()) && false ?: '_'});
