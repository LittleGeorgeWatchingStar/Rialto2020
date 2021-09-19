<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'security.authentication.listener.simple_preauth.shopify_webhook' shared service.

$this->services['security.authentication.listener.simple_preauth.shopify_webhook'] = $instance = new \Symfony\Component\Security\Http\Firewall\SimplePreAuthenticationListener(${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : ($this->services['security.token_storage'] = new \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage())) && false ?: '_'}, ${($_ = isset($this->services['security.authentication.manager']) ? $this->services['security.authentication.manager'] : $this->getSecurity_Authentication_ManagerService()) && false ?: '_'}, 'shopify_webhook', ${($_ = isset($this->services['Rialto\\Shopify\\Webhook\\WebhookAuthenticator']) ? $this->services['Rialto\\Shopify\\Webhook\\WebhookAuthenticator'] : $this->load('getWebhookAuthenticatorService.php')) && false ?: '_'}, ${($_ = isset($this->services['monolog.logger.security']) ? $this->services['monolog.logger.security'] : $this->load('getMonolog_Logger_SecurityService.php')) && false ?: '_'}, ${($_ = isset($this->services['debug.event_dispatcher']) ? $this->services['debug.event_dispatcher'] : $this->getDebug_EventDispatcherService()) && false ?: '_'});

$instance->setSessionAuthenticationStrategy(${($_ = isset($this->services['security.authentication.session_strategy.shopify_webhook']) ? $this->services['security.authentication.session_strategy.shopify_webhook'] : ($this->services['security.authentication.session_strategy.shopify_webhook'] = new \Symfony\Component\Security\Http\Session\SessionAuthenticationStrategy('none'))) && false ?: '_'});

return $instance;
