<?php

namespace Rialto\Manufacturing\Task\Web;

use Rialto\Manufacturing\Task\ProductionTaskFactory;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductionTaskController extends RialtoController
{
    /** @var ProductionTaskFactory */
    private $factory;

    /**
     * Initialize any additional properties that the controller needs.
     */
    protected function init(ContainerInterface $container)
    {
        $this->factory = $container->get(ProductionTaskFactory::class);
    }


    /**
     * Force a refresh of the tasks for a PO.
     *
     * @Route("/manufacturing/purchaseorder/{id}/tasks",
     *   name="production_task_refresh")
     * @Method("POST")
     */
    public function refreshAction(PurchaseOrder $po)
    {
        $this->denyAccessUnlessGranted(Role::STOCK_VIEW);
        $this->dbm->beginTransaction();
        try {
            $this->factory->refreshTasks($po);
            $this->dbm->flushAndCommit();
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }
        $this->logNotice("Updated tasks for $po.");
        return $this->redirectToRoute('purchase_order_view', [
            'order' => $po->getId(),
        ]);
    }
}
