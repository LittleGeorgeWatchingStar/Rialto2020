<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Rialto\Sales\Order\Dates\TargetShipDateListener' shared autowired service.

return $this->services['Rialto\\Sales\\Order\\Dates\\TargetShipDateListener'] = new \Rialto\Sales\Order\Dates\TargetShipDateListener(${($_ = isset($this->services['Rialto\\Sales\\Order\\Dates\\TargetShipDateCalculator']) ? $this->services['Rialto\\Sales\\Order\\Dates\\TargetShipDateCalculator'] : ($this->services['Rialto\\Sales\\Order\\Dates\\TargetShipDateCalculator'] = new \Rialto\Sales\Order\Dates\TargetShipDateCalculator())) && false ?: '_'});