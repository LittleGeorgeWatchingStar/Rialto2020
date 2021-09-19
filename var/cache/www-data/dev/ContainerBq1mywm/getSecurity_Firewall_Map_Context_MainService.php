<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'security.firewall.map.context.main' shared service.

$a = ${($_ = isset($this->services['security.token_storage']) ? $this->services['security.token_storage'] : ($this->services['security.token_storage'] = new \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage())) && false ?: '_'};
$b = ${($_ = isset($this->services['security.http_utils']) ? $this->services['security.http_utils'] : $this->load('getSecurity_HttpUtilsService.php')) && false ?: '_'};
$c = new \Symfony\Component\Security\Http\Firewall\LogoutListener($a, $b, ${($_ = isset($this->services['Gumstix\\SSOBundle\\Service\\LogoutService']) ? $this->services['Gumstix\\SSOBundle\\Service\\LogoutService'] : ($this->services['Gumstix\\SSOBundle\\Service\\LogoutService'] = new \Gumstix\SSOBundle\Service\LogoutService('http://accounts.mystix.com/'))) && false ?: '_'}, ['csrf_parameter' => '_csrf_token', 'csrf_token_id' => 'logout', 'logout_path' => '/logout']);
$c->addHandler(new \Symfony\Component\Security\Http\Logout\CsrfTokenClearingLogoutHandler(${($_ = isset($this->services['security.csrf.token_storage']) ? $this->services['security.csrf.token_storage'] : $this->load('getSecurity_Csrf_TokenStorageService.php')) && false ?: '_'}));
$c->addHandler(new \Symfony\Component\Security\Http\Logout\SessionLogoutHandler());

return $this->services['security.firewall.map.context.main'] = new \Symfony\Bundle\SecurityBundle\Security\FirewallContext(new RewindableGenerator(function () {
    yield 0 => ${($_ = isset($this->services['security.channel_listener']) ? $this->services['security.channel_listener'] : $this->load('getSecurity_ChannelListenerService.php')) && false ?: '_'};
    yield 1 => ${($_ = isset($this->services['security.context_listener.1']) ? $this->services['security.context_listener.1'] : $this->load('getSecurity_ContextListener_1Service.php')) && false ?: '_'};
    yield 2 => ${($_ = isset($this->services['security.authentication.listener.guard.main']) ? $this->services['security.authentication.listener.guard.main'] : $this->load('getSecurity_Authentication_Listener_Guard_MainService.php')) && false ?: '_'};
    yield 3 => ${($_ = isset($this->services['security.authentication.switchuser_listener.main']) ? $this->services['security.authentication.switchuser_listener.main'] : $this->load('getSecurity_Authentication_SwitchuserListener_MainService.php')) && false ?: '_'};
    yield 4 => ${($_ = isset($this->services['security.access_listener']) ? $this->services['security.access_listener'] : $this->load('getSecurity_AccessListenerService.php')) && false ?: '_'};
}, 5), new \Symfony\Component\Security\Http\Firewall\ExceptionListener($a, ${($_ = isset($this->services['security.authentication.trust_resolver']) ? $this->services['security.authentication.trust_resolver'] : ($this->services['security.authentication.trust_resolver'] = new \Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolver('Symfony\\Component\\Security\\Core\\Authentication\\Token\\AnonymousToken', 'Symfony\\Component\\Security\\Core\\Authentication\\Token\\RememberMeToken'))) && false ?: '_'}, $b, 'main', ${($_ = isset($this->services['Gumstix\\SSOBundle\\Security\\LoginAuthenticator']) ? $this->services['Gumstix\\SSOBundle\\Security\\LoginAuthenticator'] : $this->load('getLoginAuthenticatorService.php')) && false ?: '_'}, NULL, NULL, ${($_ = isset($this->services['monolog.logger.security']) ? $this->services['monolog.logger.security'] : $this->load('getMonolog_Logger_SecurityService.php')) && false ?: '_'}, false), $c, new \Symfony\Bundle\SecurityBundle\Security\FirewallConfig('main', 'security.user_checker', 'security.request_matcher.00qf1z7', true, false, 'Rialto\\Security\\Firewall\\ByUuidProvider', 'main', 'Gumstix\\SSOBundle\\Security\\LoginAuthenticator', NULL, NULL, [0 => 'switch_user', 1 => 'guard'], ['role' => 'ROLE_ADMIN', 'parameter' => '_switch_user', 'stateless' => false]));