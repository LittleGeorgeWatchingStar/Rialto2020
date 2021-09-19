<?php

namespace Rialto\Manufacturing\PurchaseOrder;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Manufacturing\WorkOrder\Orm\WorkOrderRepository;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Order\Event\PurchaseOrderSent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * If a PO for parts is sent to the supplier, this listener updates
 * any work orders depending on those parts.
 *
 * We need to do this to regenerate the production tasks of the affected
 * work orders.
 */
class PartsOrderSentListener implements EventSubscriberInterface
{
    /** @var WorkOrderRepository */
    private $repo;

    public function __construct(ObjectManager $om)
    {
        $this->repo = $om->getRepository(WorkOrder::class);
    }


    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents()
    {
        return [
            PurchaseOrderSent::class => 'updateDependentOrders',
        ];
    }

    public function updateDependentOrders(PurchaseOrderSent $event)
    {
        /** @var $workOrders WorkOrder[] */
        $workOrders = $this->repo->createBuilder()
            ->isOpen()
            ->isAllocatedFromOrder($event->getPurchaseOrder())
            ->getResult();
        foreach ($workOrders as $wo) {
            $wo->setUpdated();
        }
    }
}
