<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the private 'Rialto\Shopify\Webhook\WebhookAuthenticator' shared autowired service.

return $this->services['Rialto\\Shopify\\Webhook\\WebhookAuthenticator'] = new \Rialto\Shopify\Webhook\WebhookAuthenticator(${($_ = isset($this->services['Doctrine\\Common\\Persistence\\ObjectManager']) ? $this->services['Doctrine\\Common\\Persistence\\ObjectManager'] : $this->getObjectManagerService()) && false ?: '_'});
