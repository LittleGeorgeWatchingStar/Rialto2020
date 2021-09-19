<?php

namespace Rialto\Warehouse\Web;

use Rialto\Allocation\Allocation\Orm\StockAllocationRepository;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Purchasing\Order\Orm\PurchaseOrderRepository;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Sales\Order\Orm\SalesOrderRepository;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Security\Role\Role;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Transfer\Orm\TransferItemRepository;
use Rialto\Stock\Transfer\Orm\TransferRepository;
use Rialto\Stock\Transfer\Transfer;
use Rialto\Stock\Transfer\TransferItem;
use Rialto\Stock\Transfer\Web\TransferController;
use Rialto\Summary\Menu\Main\PleaseAssembleSummary;
use Rialto\Summary\Menu\Main\SalesReturnSummary;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;

class DashboardController extends RialtoController
{
    /** @var PurchaseOrderRepository */
    private $poRepo;

    /** @var SalesOrderRepository */
    private $soRepo;

    /** @var TransferRepository */
    private $trRepo;

    /** @var TransferItemRepository */
    private $triRepo;

    /** @var StockAllocationRepository */
    private $allocationRepo;

    /** @var RouterInterface */
    private $router;

    /**
     * Initialize any additional properties that the controller needs.
     */
    protected function init(ContainerInterface $container)
    {
        $this->poRepo = $this->getRepository(PurchaseOrder::class);
        $this->soRepo = $this->getRepository(SalesOrder::class);
        $this->trRepo = $this->getRepository(Transfer::class);
        $this->triRepo = $this->getRepository(TransferItem::class);
        $this->allocationRepo = $this->getRepository(StockAllocation::class);
        $this->router = $this->get(RouterInterface::class);
    }

    /**
     * @Route("/warehouse/", name="warehouse_dashboard")
     * @Method("GET")
     * @Template("warehouse/dashboard.html.twig")
     */
    public function indexAction()
    {
        $this->denyAccessUnlessGranted([Role::WAREHOUSE, Role::MANUFACTURING, Role::PURCHASING]);
        $this->setReturnUri($this->getCurrentUri());

        $purchOrders = $this->poRepo->findRequiredTasksForWarehouse();
        $salesReturns = new SalesReturnSummary($this->dbm, $this->router);
        $pleaseAssemble = new PleaseAssembleSummary($this->dbm, $this->router);

        $transferCtrl = TransferController::class;

        return [
            'purchOrders' => $purchOrders,
            'salesOrders' => $this->salesOrders(),
            'salesReturns' => $salesReturns->getChildren(),
            'pleaseAssemble' => $pleaseAssemble->getChildren(),
            'willBeTransferred' => $this->willBeTransferred(),
            'pleaseKit' => $this->pleaseKit(),
            'needTrackingNumber' => $this->requireTrackingNumber(),
            'awaitingPickup' => $this->awaitingPickup(),
            'missingInTransit' => $this->missingInTransit(),
            'markAsSent' => "$transferCtrl::sentAction",
            'inputtrackingnum' => "$transferCtrl::inputTrackingNumberAction",
        ];
    }

    private function salesOrders()
    {
        return $this->soRepo->createBuilder()
            ->isNotComplete()
            ->isApprovedToShip()
            ->byLocation(Facility::HEADQUARTERS_ID)
            ->bySalesStage(SalesOrder::ORDER)
            ->hasPartsInStock()
            ->getResult();
    }

    private function willBeTransferred()
    {
        $allocToBeTransferred = $this->allocationRepo->getToBeTransferred();
        $results = [];
        foreach ($allocToBeTransferred as $allocation) {
            /** @var WorkOrder $wo */
            $wo = $allocation->getConsumer();
            $poId = $wo->getPurchaseOrder()->getId();
            if (isset($results[$poId])) {
                $results[$poId][] = $allocation;
            } else {
                $results[$poId] = [$allocation];
            }
        }

        return $results;
    }

    private function pleaseKit()
    {
        $user = $this->getCurrentUser();
        return $this->trRepo->createBuilder()
            ->byOrigin($user->getDefaultLocation() ? : Facility::HEADQUARTERS_ID)
            ->notKitted()
            ->notEmpty()
            ->getResult();
    }

    private function requireTrackingNumber()
    {
        $user = $this->getCurrentUser();
        $shippingMethods = [$this->trRepo::HAND_CARRIED, $this->trRepo::TRUCK];
        if ($user->getDefaultLocation() === null) {
            // use Headquarter As Default -> new Facility(Facility::HEADQUARTERS_ID)
            return $this->trRepo->findTransferRequiresTrackingNumber(new Facility(Facility::HEADQUARTERS_ID), $shippingMethods);
        } else {
            return $this->trRepo->findTransferRequiresTrackingNumber($user->getDefaultLocation(), $shippingMethods);
        }
    }

    private function awaitingPickup()
    {
        $user = $this->getCurrentUser();
        return $this->trRepo->createBuilder()
            ->byOrigin($user->getDefaultLocation() ? : Facility::HEADQUARTERS_ID)
            ->kitted()
            ->isReadyForPickup($this->trRepo::HAND_CARRIED)
            ->notSent()
            ->notReceived()
            ->getResult();
    }

    private function missingInTransit()
    {
        return $this->triRepo->createBuilder()
            ->missing()
            ->inTransit()
            ->orderBySku()
            ->getResult();
    }
}
