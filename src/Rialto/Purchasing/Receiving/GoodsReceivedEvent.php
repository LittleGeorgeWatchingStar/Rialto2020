<?php

namespace Rialto\Purchasing\Receiving;

use Rialto\Security\User\User;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is dispatched when an order is received.
 */
class GoodsReceivedEvent extends Event
{
    /** @var GoodsReceivedNotice */
    private $grn;

    public function __construct(GoodsReceivedNotice $grn)
    {
        $this->grn = $grn;
    }

    /** @return GoodsReceivedNotice */
    public function getGrn()
    {
        return $this->grn;
    }

    /**
     * @return bool
     */
    public function shouldNotifyOwner()
    {
        // Don't pester about in-house assembly orders.
        return $this->getOrder()->hasSupplier();
    }

    private function getOrder()
    {
        return $this->grn->getPurchaseOrder();
    }

    /**
     * @return User
     */
    public function getOrderOwner()
    {
        return $this->getOrder()->getOwner();
    }
}
