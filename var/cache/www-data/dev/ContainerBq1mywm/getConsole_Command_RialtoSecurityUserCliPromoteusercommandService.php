<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'console.command.rialto_security_user_cli_promoteusercommand' shared autowired service.

return $this->services['console.command.rialto_security_user_cli_promoteusercommand'] = new \Rialto\Security\User\Cli\PromoteUserCommand(${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'});
