<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'service_locator.yscbjmj' shared service.

return $this->services['service_locator.yscbjmj'] = new \Symfony\Component\DependencyInjection\ServiceLocator(['doctrine' => function () {
    return ${($_ = isset($this->services['doctrine']) ? $this->services['doctrine'] : $this->getDoctrineService()) && false ?: '_'};
}, 'form.factory' => function () {
    return ${($_ = isset($this->services['form.factory']) ? $this->services['form.factory'] : $this->load('getForm_FactoryService.php')) && false ?: '_'};
}, 'http_kernel' => function () {
    return ${($_ = isset($this->services['http_kernel']) ? $this->services['http_kernel'] : $this->getHttpKernelService()) && false ?: '_'};
}, 'request_stack' => function () {
    return ${($_ = isset($this->services['request_stack']) ? $this->services['request_stack'] : ($this->services['request_stack'] = new \Symfony\Component\HttpFoundation\RequestStack())) && false ?: '_'};
}, 'router' => function () {
    return ${($_ = isset($this->services['Symfony\\Component\\Routing\\RouterInterface']) ? $this->services['Symfony\\Component\\Routing\\RouterInterface'] : $this->getRouterInterfaceService()) && false ?: '_'};
}, 'security.authorization_checker' => function () {
    return ${($_ = isset($this->services['security.authorization_checker']) ? $this->services['security.authorization_checker'] : $this->getSecurity_AuthorizationCheckerService()) && false ?: '_'};
}, 'security.csrf.token_manager' => function () {
    return ${($_ = isset($this->services['security.csrf.token_manager']) ? $this->services['security.csrf.token_manager'] : $this->load('getSecurity_Csrf_TokenManagerService.php')) && false ?: '_'};
}, 'security.token_storage' => function () {
    return ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : ($this->services['security.token_storage'] = new \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage())) && false ?: '_'};
}, 'serializer' => function () {
    return ${($_ = isset($this->services['serializer']) ? $this->services['serializer'] : $this->load('getSerializerService.php')) && false ?: '_'};
}, 'session' => function () {
    return ${($_ = isset($this->services['session']) ? $this->services['session'] : $this->load('getSessionService.php')) && false ?: '_'};
}, 'templating' => function () {
    return ${($_ = isset($this->services['templating']) ? $this->services['templating'] : $this->load('getTemplatingService.php')) && false ?: '_'};
}, 'twig' => function () {
    return ${($_ = isset($this->services['twig']) ? $this->services['twig'] : $this->getTwigService()) && false ?: '_'};
}]);
