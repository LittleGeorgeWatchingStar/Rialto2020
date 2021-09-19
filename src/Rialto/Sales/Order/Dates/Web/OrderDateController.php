<?php

namespace Rialto\Sales\Order\Dates\Web;

use Doctrine\ORM\EntityManagerInterface;
use Rialto\Allocation\Requirement\RequirementTask\RequirementTask;
use Rialto\Allocation\Requirement\RequirementTask\RequirementTaskFactory;
use Rialto\Manufacturing\ClearToBuild\ClearToBuildEstimate;
use Rialto\Manufacturing\ClearToBuild\ClearToBuildFactory;
use Rialto\Sales\Order\Orm\SalesOrderRepository;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class OrderDateController extends RialtoController
{
    /** @var SalesOrderRepository */
    private $repo;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repo = $entityManager->getRepository(SalesOrder::class);
    }
    /**
     * Show sales orders with an emphasis on target ship date.
     *
     * @Route("/sales/dashboard/order-ship-date/",
     *     name="target_ship_date_dashboard")
     * @Template("sales/order/ship-date.html.twig")
     */
    public function targetDatesAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $qb = $this->repo->createBuilder()
            ->hasTargetShipDate()
            ->isNotComplete();
        if (($sku = $request->get('sku'))) {
            $qb->bySku($sku);
        }
        /** @var SalesOrder[] $orders */
        $orders = $qb->getResult();

        /** @var RequirementTaskFactory $requirementTasksFactory */
        $requirementTasksFactory = $this->get(RequirementTaskFactory::class);
        /** @var ClearToBuildFactory $clearToBuild */
        $clearToBuild = $this->get(ClearToBuildFactory::class);
        /** @var RequirementTask[] $poRequirementTasks */
        $poRequirementTasks = [];
        /** @var ClearToBuildEstimate[] $poClearToBuildEstimates */
        $poClearToBuildEstimates = [];

        foreach ($orders as $order) {
            foreach ($order->getAllocationStatus()->getProducers() as $producer) {
                $po = $producer->getPurchaseOrder();
                $poRequirementTasks[$po->getId()] = $requirementTasksFactory->getPurchaseOrderRequirementTasks($po);
                $poClearToBuildEstimates[$po->getId()] = $clearToBuild->getEstimateForPurchaseOrder($po);
            }
        }

        return [
            'orders' => $orders,
            'poRequirementTasks' => $poRequirementTasks,
            'poClearToBuildEstimates' => $poClearToBuildEstimates,
        ];
    }
}
