<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Rialto\Manufacturing\Customization\Web\CustomizationStrategyType' shared autowired service.

return $this->services['Rialto\\Manufacturing\\Customization\\Web\\CustomizationStrategyType'] = new \Rialto\Manufacturing\Customization\Web\CustomizationStrategyType(${($_ = isset($this->services['Rialto\\Manufacturing\\Customization\\Customizer']) ? $this->services['Rialto\\Manufacturing\\Customization\\Customizer'] : $this->load('getCustomizerService.php')) && false ?: '_'});
