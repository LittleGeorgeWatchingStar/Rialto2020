<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'console.command.rialto_accounting_paymenttransaction_cli_recalculatesettled' shared autowired service.

return $this->services['console.command.rialto_accounting_paymenttransaction_cli_recalculatesettled'] = new \Rialto\Accounting\PaymentTransaction\Cli\RecalculateSettled(${($_ = isset($this->services['doctrine.orm.default_entity_manager']) ? $this->services['doctrine.orm.default_entity_manager'] : $this->getDoctrine_Orm_DefaultEntityManagerService()) && false ?: '_'});
