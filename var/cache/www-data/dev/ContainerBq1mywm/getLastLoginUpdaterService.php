<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Rialto\Security\User\LastLoginUpdater' shared autowired service.

return $this->services['Rialto\\Security\\User\\LastLoginUpdater'] = new \Rialto\Security\User\LastLoginUpdater(${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'});
