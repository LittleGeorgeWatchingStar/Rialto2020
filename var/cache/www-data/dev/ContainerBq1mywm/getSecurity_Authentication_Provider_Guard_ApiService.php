<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'security.authentication.provider.guard.api' shared service.

return $this->services['security.authentication.provider.guard.api'] = new \Symfony\Component\Security\Guard\Provider\GuardAuthenticationProvider(new RewindableGenerator(function () {
    yield 0 => ${($_ = isset($this->services['Gumstix\\SSOBundle\\Security\\CookieAuthenticator']) ? $this->services['Gumstix\\SSOBundle\\Security\\CookieAuthenticator'] : $this->load('getCookieAuthenticatorService.php')) && false ?: '_'};
    yield 1 => ${($_ = isset($this->services['Gumstix\\SSOBundle\\Security\\HeaderAuthenticator']) ? $this->services['Gumstix\\SSOBundle\\Security\\HeaderAuthenticator'] : $this->load('getHeaderAuthenticatorService.php')) && false ?: '_'};
}, 2), ${($_ = isset($this->services['Rialto\\Security\\Firewall\\ByUuidProvider']) ? $this->services['Rialto\\Security\\Firewall\\ByUuidProvider'] : $this->load('getByUuidProviderService.php')) && false ?: '_'}, 'api', ${($_ = isset($this->services['security.user_checker']) ? $this->services['security.user_checker'] : ($this->services['security.user_checker'] = new \Symfony\Component\Security\Core\User\UserChecker())) && false ?: '_'});
