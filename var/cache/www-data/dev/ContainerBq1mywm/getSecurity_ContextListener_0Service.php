<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'security.context_listener.0' shared service.

$this->services['security.context_listener.0'] = $instance = new \Symfony\Component\Security\Http\Firewall\ContextListener(${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : ($this->services['security.token_storage'] = new \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage())) && false ?: '_'}, new RewindableGenerator(function () {
    yield 0 => ${($_ = isset($this->services['Rialto\\Security\\Firewall\\ByUuidProvider']) ? $this->services['Rialto\\Security\\Firewall\\ByUuidProvider'] : $this->load('getByUuidProviderService.php')) && false ?: '_'};
    yield 1 => ${($_ = isset($this->services['Rialto\\Security\\Firewall\\ByUsernameProvider']) ? $this->services['Rialto\\Security\\Firewall\\ByUsernameProvider'] : $this->load('getByUsernameProviderService.php')) && false ?: '_'};
    yield 2 => ${($_ = isset($this->services['Rialto\\Magento2\\Firewall\\StorefrontUserProvider']) ? $this->services['Rialto\\Magento2\\Firewall\\StorefrontUserProvider'] : $this->load('getStorefrontUserProviderService.php')) && false ?: '_'};
    yield 3 => ${($_ = isset($this->services['Rialto\\Shopify\\Webhook\\ShopifyUserProvider']) ? $this->services['Rialto\\Shopify\\Webhook\\ShopifyUserProvider'] : $this->load('getShopifyUserProviderService.php')) && false ?: '_'};
}, 4), 'api', ${($_ = isset($this->services['monolog.logger.security']) ? $this->services['monolog.logger.security'] : $this->load('getMonolog_Logger_SecurityService.php')) && false ?: '_'}, ${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->getDebug_EventDispatcherService()) && false ?: '_'}, ${($_ = isset($this->services['security.authentication.trust_resolver']) ? $this->services['security.authentication.trust_resolver'] : ($this->services['security.authentication.trust_resolver'] = new \Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver('Symfony\\Component\\Security\\Core\\Authentication\\Token\\AnonymousToken', 'Symfony\\Component\\Security\\Core\\Authentication\\Token\\RememberMeToken'))) && false ?: '_'});

$instance->setLogoutOnUserChange(true);

return $instance;
