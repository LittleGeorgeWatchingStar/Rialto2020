<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Rialto\Magento2\Firewall\StorefrontUserProvider' shared autowired service.

return $this->services['Rialto\\Magento2\\Firewall\\StorefrontUserProvider'] = new \Rialto\Magento2\Firewall\StorefrontUserProvider(${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'});