<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Rialto\Security\User\Web\UserType' shared autowired service.

return $this->services['Rialto\\Security\\User\\Web\\UserType'] = new \Rialto\Security\User\Web\UserType(${($_ = isset($this->services['security.authorization_checker']) ? $this->services['security.authorization_checker'] : $this->getSecurity_AuthorizationCheckerService()) && false ?: '_'});
