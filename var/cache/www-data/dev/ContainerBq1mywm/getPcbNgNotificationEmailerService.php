<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Rialto\PcbNg\Service\PcbNgNotificationEmailer' shared autowired service.

return $this->services['Rialto\\PcbNg\\Service\\PcbNgNotificationEmailer'] = new \Rialto\PcbNg\Service\PcbNgNotificationEmailer(${($_ = isset($this->services['Rialto\\Email\\MailerInterface']) ? $this->services['Rialto\\Email\\MailerInterface'] : $this->load('getMailerInterfaceService.php')) && false ?: '_'});
