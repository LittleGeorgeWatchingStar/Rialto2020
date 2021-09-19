<?php

namespace Rialto\Manufacturing\Task;


use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Rialto\Purchasing\Order\PurchaseOrder;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Listen for events that require production tasks to be regenerated.
 */
class ProductionTaskRefreshListener implements EventSubscriberInterface
{
    /** @var EventDispatcherInterface */
    private $dispatcher;

    /** @var EntityManagerInterface */
    private $em = null;

    /** @var PurchaseOrder[] */
    private $purchaseOrders = [];

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->collectPurchaseOrders($args);
    }

    /**
     * Hold any modified work order POs until the end of the request. They'll
     * be processed in onKernelTerminate() below.
     */
    private function collectPurchaseOrders(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (($entity instanceof PurchaseOrder) && $entity->hasWorkOrders() && (!$entity->isCompleted())) {
            $this->em = $args->getObjectManager();
            $id = $entity->getId();
            $this->purchaseOrders[$id] = $entity;
        }
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2'))
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::TERMINATE => 'refreshTasksAndJobs',
            ConsoleEvents::TERMINATE => 'refreshTasksAndJobs',
        ];
    }

    /**
     * Once the request is finished, update the tasks and jobs for any
     * modified purchase orders.
     */
    public function refreshTasksAndJobs($event = null)
    {
        if (count($this->purchaseOrders) == 0) {
            return;
        }

        $taskFactory = new ProductionTaskFactory($this->em, $this->dispatcher);
        $jobFactory = new JobFactory($this->em);

        $this->em->beginTransaction();
        try {
            foreach ($this->purchaseOrders as $po) {
                $taskFactory->refreshTasks($po);
                $jobFactory->forPurchaseOrder($po);
            }
            $this->em->flush();
            $this->em->commit();
            $this->purchaseOrders = [];
        } catch (\Exception $ex) {
            $this->em->rollback();
            throw $ex;
        }
    }
}
