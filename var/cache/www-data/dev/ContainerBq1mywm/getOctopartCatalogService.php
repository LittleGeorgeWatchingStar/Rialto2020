<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Purchasing\Catalog\Remote\OctopartCatalog' shared autowired service.

return $this->services['Rialto\\Purchasing\\Catalog\\Remote\\OctopartCatalog'] = new \Rialto\Purchasing\Catalog\Remote\OctopartCatalog(new \GuzzleHttp\Client(), new \Rialto\Purchasing\Catalog\Remote\OctopartCatalogParser(${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'}), '');
