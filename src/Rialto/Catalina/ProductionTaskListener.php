<?php

namespace Rialto\Catalina;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Log\LoggerInterface;
use Rialto\Manufacturing\ManufacturingEvents;
use Rialto\Manufacturing\Task\ProductionTask;
use Rialto\Manufacturing\Task\ProductionTaskEvent;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Security\Role\Role;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Adds Catalina-related production tasks to a PO.
 */
class ProductionTaskListener implements EventSubscriberInterface
{
    /** @var CatalinaClient */
    private $catalina;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(CatalinaClient $catalina, LoggerInterface $logger)
    {
        $this->catalina = $catalina;
        $this->logger = $logger;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     **
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     */
    public static function getSubscribedEvents()
    {
        return [
            ManufacturingEvents::ADD_PRODUCTION_TASKS => 'addProductionTasks',
        ];
    }

    public function addProductionTasks(ProductionTaskEvent $event)
    {
        foreach ($event->getWorkOrders() as $wo) {
            if ($wo->isBoard()) {
                $this->addJobTaskIfNeeded($wo, $event);
            }
        }
    }

    private function addJobTaskIfNeeded(WorkOrder $wo, ProductionTaskEvent $event)
    {
        try {
            $jobData = $this->catalina->getJob($wo);
        } catch (GuzzleException $ex) {
            $this->logger->error("Error communicating with Catalina: {$ex->getMessage()}");
            return;
        }
        if (!$jobData) {
            $event->addTask(new ProductionTask('Job', 'catalina_job_create', [
                'order' => $wo->getId(),
            ], [
                Role::MANUFACTURING,
                Role::STOCK,
            ]));
        }
    }
}
