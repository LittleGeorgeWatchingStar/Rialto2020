<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'console.command.rialto_purchasing_receiving_notify_testxmppcommand' shared autowired service.

return $this->services['console.command.rialto_purchasing_receiving_notify_testxmppcommand'] = new \Rialto\Purchasing\Receiving\Notify\TestXmppCommand(${($_ = isset($this->services['Fabiang\\Xmpp\\Client']) ? $this->services['Fabiang\\Xmpp\\Client'] : $this->load('getClientService.php')) && false ?: '_'});
