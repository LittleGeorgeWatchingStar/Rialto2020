<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Rialto\Purchasing\Receiving\Auth\ReceiveIntoVoter' shared autowired service.

return $this->services['Rialto\\Purchasing\\Receiving\\Auth\\ReceiveIntoVoter'] = new \Rialto\Purchasing\Receiving\Auth\ReceiveIntoVoter(${($_ = isset($this->services['security.role_hierarchy']) ? $this->services['security.role_hierarchy'] : $this->load('getSecurity_RoleHierarchyService.php')) && false ?: '_'});