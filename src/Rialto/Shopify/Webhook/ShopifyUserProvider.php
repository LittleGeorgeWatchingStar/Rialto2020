<?php

namespace Rialto\Shopify\Webhook;


use Rialto\Security\Firewall\UserProvider;

/**
 * Symfony requires each firewall to have a user provider, but Shopify
 * doesn't really need it.
 */
class ShopifyUserProvider extends UserProvider
{
    protected function findUserIfExists(string $username)
    {
        throw new \LogicException("Shopify doesn't need ". __METHOD__);
    }
}
