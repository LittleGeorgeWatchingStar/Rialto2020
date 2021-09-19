<?php

namespace Rialto\Magento2\Firewall;


use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Magento2\Storefront\Storefront;
use Rialto\Magento2\Storefront\StorefrontRepository;
use Rialto\Security\Firewall\UserProvider;
use Rialto\Security\User\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class StorefrontUserProvider extends UserProvider
{
    /** @var StorefrontRepository */
    private $storefrontRepo;

    public function __construct(ObjectManager $om)
    {
        parent::__construct($om);
        $this->storefrontRepo = $om->getRepository(Storefront::class);
    }

    protected function findUserIfExists(string $apiKey)
    {
        $store = $this->getStorefront($apiKey);
        if (!$store) {
            throw new UsernameNotFoundException('API Key provided does not exist.');
        }
        $user = $store->getUser();
        assert($user instanceof User);
        return $user;
    }

    /**
     * @return Storefront|null
     */
    private function getStorefront(string $apiKey)
    {
        return $this->storefrontRepo->findOneBy(['apiKey' => $apiKey]);
    }
}
