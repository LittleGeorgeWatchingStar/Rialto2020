<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Manufacturing\ClearToBuild\ClearToBuildFactory' shared autowired service.

return $this->services['Rialto\\Manufacturing\\ClearToBuild\\ClearToBuildFactory'] = new \Rialto\Manufacturing\ClearToBuild\ClearToBuildFactory(${($_ = isset($this->services['Rialto\\Purchasing\\Producer\\CommitmentDateEstimator\\StockProducerCommitmentDateEstimator']) ? $this->services['Rialto\\Purchasing\\Producer\\CommitmentDateEstimator\\StockProducerCommitmentDateEstimator'] : $this->getStockProducerCommitmentDateEstimatorService()) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Shipping\\Method\\ShippingTimeEstimator\\ShippingTimeEstimatorInterface']) ? $this->services['Rialto\\Shipping\\Method\\ShippingTimeEstimator\\ShippingTimeEstimatorInterface'] : ($this->services['Rialto\\Shipping\\Method\\ShippingTimeEstimator\\ShippingTimeEstimatorInterface'] = new \Rialto\Shipping\Method\ShippingTimeEstimator\ShippingTimeEstimator())) && false ?: '_'});
