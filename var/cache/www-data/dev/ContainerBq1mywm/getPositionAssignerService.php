<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Stock\Shelf\Position\PositionAssigner' shared autowired service.

$a = ${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'};

return $this->services['Rialto\\Stock\\Shelf\\Position\\PositionAssigner'] = new \Rialto\Stock\Shelf\Position\PositionAssigner($a, new \Rialto\Stock\Shelf\Velocity\VelocityCalculator($a), new \Rialto\Stock\Shelf\Position\Query\DQL\DqlFirstAvailablePositionQuery($a));