<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Rialto\Geography\Address\Web\AddressEntityType' shared autowired service.

return $this->services['Rialto\\Geography\\Address\\Web\\AddressEntityType'] = new \Rialto\Geography\Address\Web\AddressEntityType(new \Rialto\Geography\Address\Web\AddressToArrayTransformer(${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'}));
