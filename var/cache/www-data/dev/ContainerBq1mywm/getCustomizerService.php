<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Rialto\Manufacturing\Customization\Customizer' shared autowired service.

$this->services['Rialto\\Manufacturing\\Customization\\Customizer'] = $instance = new \Rialto\Manufacturing\Customization\Customizer();

$instance->register('ext-temp', new \Rialto\Manufacturing\Customization\ExtendedTemperatureCustomization(${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'}, -40, 85));

return $instance;
