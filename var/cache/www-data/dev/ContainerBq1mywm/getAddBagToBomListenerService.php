<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Manufacturing\Bom\Bag\AddBagToBomListener' shared autowired service.

return $this->services['Rialto\\Manufacturing\\Bom\\Bag\\AddBagToBomListener'] = new \Rialto\Manufacturing\Bom\Bag\AddBagToBomListener(new \Rialto\Manufacturing\Bom\Bag\BagAdder(new \Rialto\Manufacturing\Bom\Bag\BagFinder(${($_ = isset($this->services['doctrine']) ? $this->services['doctrine'] : $this->getDoctrineService()) && false ?: '_'}->getRepository('Rialto\\Stock\\Item\\Version\\ItemVersion')), ${($_ = isset($this->services['Rialto\\Email\\MailerInterface']) ? $this->services['Rialto\\Email\\MailerInterface'] : $this->load('getMailerInterfaceService.php')) && false ?: '_'}));
