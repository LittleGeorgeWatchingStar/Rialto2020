<?php

namespace Rialto\Magento2\Order;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Magento2\Storefront\Storefront;
use Rialto\Magento2\Storefront\StorefrontRepository;
use Rialto\Sales\Order\SalesOrder;


/**
 * Base class for listeners that are listening for sales order events.
 */
abstract class OrderListener
{
    /** @var StorefrontRepository */
    private $repo;

    public function __construct(ObjectManager $om)
    {
        $this->repo = $om->getRepository(Storefront::class);
    }

    /** @return Storefront|null */
    protected function getStorefront(SalesOrder $order)
    {
        $creator = $order->getCreatedBy();
        return $this->repo->findByUserIfExists($creator);
    }
}
