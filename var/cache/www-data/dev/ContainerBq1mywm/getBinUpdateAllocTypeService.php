<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Rialto\Stock\Bin\Web\BinUpdateAllocType' shared autowired service.

return $this->services['Rialto\\Stock\\Bin\\Web\\BinUpdateAllocType'] = new \Rialto\Stock\Bin\Web\BinUpdateAllocType(${($_ = isset($this->services['security.authorization_checker']) ? $this->services['security.authorization_checker'] : $this->getSecurity_AuthorizationCheckerService()) && false ?: '_'});
