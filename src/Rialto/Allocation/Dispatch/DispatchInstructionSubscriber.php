<?php

namespace Rialto\Allocation\Dispatch;

use Rialto\Allocation\Dispatch\Web\DispatchInstructionsController;
use Rialto\Purchasing\PurchasingEvents;
use Rialto\Purchasing\Receiving\GoodsReceivedEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


/**
 * Handles events that generate allocation dispatch instructions.
 *
 * If stock is allocated to something, the dispatch instructions tell the
 * folks in the warehouse what it is allocated to and what to do with it.
 */
class DispatchInstructionSubscriber implements EventSubscriberInterface
{
    /** @var SessionInterface */
    private $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }

    public static function getSubscribedEvents()
    {
        return [
            PurchasingEvents::GOODS_RECEIVED => 'onGoodsReceived',
        ];
    }

    public function onGoodsReceived(GoodsReceivedEvent $event)
    {
        $grn = $event->getGrn();
        $instructions = new AllocationDispatchInstructions();
        foreach ($grn->getItems() as $grnItem) {
            $allocations = $grnItem->getAllocationsReceived();
            if (count($allocations) > 0) {
                $instructions->addAllocations(
                    $allocations,
                    $grnItem->getQtyReceived());
            }
        }
        if (count($instructions) > 0) {
            $key = DispatchInstructionsController::SESSION_KEY;
            $this->session->set($key, $instructions);
        }
    }

}
