<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'Rialto\Payment\Sweep\CardTransactionSweep' shared autowired service.

return $this->services['Rialto\\Payment\\Sweep\\CardTransactionSweep'] = new \Rialto\Payment\Sweep\CardTransactionSweep(${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Payment\\AuthorizeNet']) ? $this->services['Rialto\\Payment\\AuthorizeNet'] : $this->load('getAuthorizeNetService.php')) && false ?: '_'}, ${($_ = isset($this->services['Rialto\\Accounting\\Bank\\Account\\Repository\\BankAccountRepository']) ? $this->services['Rialto\\Accounting\\Bank\\Account\\Repository\\BankAccountRepository'] : $this->load('getBankAccountRepositoryService.php')) && false ?: '_'});
