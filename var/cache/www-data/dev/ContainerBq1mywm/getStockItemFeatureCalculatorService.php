<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Madison\Feature\StockItemFeatureCalculator' shared autowired service.

return $this->services['Rialto\\Madison\\Feature\\StockItemFeatureCalculator'] = new \Rialto\Madison\Feature\StockItemFeatureCalculator(${($_ = isset($this->services['Rialto\\Madison\\Feature\\Repository\\StockItemFeatureRepository']) ? $this->services['Rialto\\Madison\\Feature\\Repository\\StockItemFeatureRepository'] : $this->load('getStockItemFeatureRepositoryService.php')) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Manufacturing\\Customization\\Customizer']) ? $this->services['Rialto\\Manufacturing\\Customization\\Customizer'] : $this->load('getCustomizerService.php')) && false ?: '_'});
