<?php

namespace Rialto\Sales\Order\Allocation\Web;

use Exception;
use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Manufacturing\Requirement\Requirement;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Port\CommandBus\CommandBus;
use Rialto\Purchasing\Order\PurchaseOrderFactory;
use Rialto\Purchasing\Producer\StockProducerFactory;
use Rialto\Sales\Order\Allocation\AllocatorGroup;
use Rialto\Sales\Order\Allocation\Command\CreateStockItemOrderCommand;
use Rialto\Sales\Order\Allocation\SalesOrderDetailAllocator;
use Rialto\Sales\Order\Allocation\SalesOrderDetailAllocatorManufactured;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Sales\Order\SalesOrderEvent;
use Rialto\Sales\Order\WhereToBuild\WhereToBuild;
use Rialto\Sales\SalesEvents;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Request;
use UnexpectedValueException;

/**
 * For managing sales order items.
 */
class SalesOrderAllocationController extends RialtoController
{
    /** @var CommandBus */
    private $bus;

    /** @var $factory PurchaseOrderFactory */
    private $purchaseOrderFactory;

    protected function init(ContainerInterface $container)
    {
        $this->bus = $container->get(CommandBus::class);
        $this->purchaseOrderFactory = $this->get(PurchaseOrderFactory::class);
    }

    /**
     * @Route("/sales/order-item/{id}/allocations/",
     *   name="sales_orderitem_allocate")
     * @Template("sales/order/item/allocate.html.twig")
     */
    public function allocateAction(SalesOrderDetail $lineItem, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::WAREHOUSE]);

        if ($lineItem->isDummy()) {
            throw $this->badRequest("Cannot allocate stock for dummy items");
        } elseif ($lineItem->isCompleted()) {
            throw $this->badRequest("Line item is completed.");
        }

        $order = $lineItem->getSalesOrder();

        $actions = $request->get('actions');
        $actions = explode(',', $actions);

        $container = new AllocatorGroup($lineItem, $this->dbm);

        try {
            $whereToBuild = new WhereToBuild($lineItem, $this->dbm);
            $container->setBuildLocations($whereToBuild->getBuildFacilities());
        } catch (Exception $exception) {
            $whereToBuild = null;
        }


        $options = $this->getFormOptions($actions);
        $form = $this->createFormBuilder($container, $options)
            ->add('allocators', CollectionType::class, [
                'entry_type' => SalesOrderDetailAllocatorType::class,
                'entry_options' => $options,
                'by_reference' => true,
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $qtyAllocated = $this->createAllocations($container, $actions);
                if ($qtyAllocated > 0) {
                    $this->notifyOfAllocation($order);
                }
                $this->dbm->flush();
                $this->dbm->flushAndCommit();
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            return $this->redirect($this->getCurrentUri());
        }



        return [
            'order' => $order,
            'item' => $lineItem->getStockItem(),
            'consumer' => $lineItem,
            'allocators' => $container,
            'whereToBuild' => $whereToBuild,
            'form' => $form->createView(),
        ];
    }

    private function getFormOptions(array $actions)
    {
        $validationGroups = ['Default'];
        if (in_array('new', $actions)) {
            $validationGroups[] = 'purchasing';
        }

        return [
            'validation_groups' => $validationGroups,
        ];
    }

    /**
     * @param SalesOrderDetailAllocator[] $allocators
     * @return int The quantity allocated
     */
    private function createAllocations(AllocatorGroup $allocators, array $actions)
    {
        /** @var $allocFactory AllocationFactory */
        $allocFactory = $this->get(AllocationFactory::class);
        /** @var $producerFactory StockProducerFactory */
        $producerFactory = $this->get(StockProducerFactory::class);
        $qtyAllocated = 0;

        foreach ($allocators as $allocator) {
            if (!$allocator->isSelected()) {
                continue;
            }
            foreach ($actions as $action) {
                switch ($action) {
                    case "delete":
                        $allocator->deleteAllocations();
                        break;
                    case "stock":
                        $qtyAllocated += $allocator->allocateFromStock($allocFactory);
                        break;
                    case "orders":
                        $qtyAllocated += $allocator->allocateFromOrders($allocFactory);
                        break;
                    case "new":
                        $producer = $producerFactory->createForSalesOrderDetail($allocator);
                        $this->logNotice("Created $producer.");
                        $qtyAllocated += $allocator->allocateFromNewOrder($allocFactory, $producer);

                        if ($producer->isWorkOrder()) {
                            if ($producer->isBoard()) {
                                /** @var WorkOrder $producer */
                                $requirements = $producer->getRequirements();
                                foreach ($requirements as $requirement) {
                                    $this->handlePcbRequirement($requirement, $allocator);
                                }
                            } elseif ($producer->isProduct()) {
                                /** @var WorkOrder $producer */
                                $requirements = $producer->getRequirements();
                                foreach ($requirements as $requirement) {
                                    if ($requirement->isBoard()) {
                                        $sourceOrder = $requirement->getSourceWorkOrder();
                                        if ($sourceOrder !== null) {
                                            $brdRequirements = $sourceOrder->getRequirements();
                                        } else {
                                            $brdRequirements = $requirement->getOrder()->getRequirements();
                                        }

                                        if ($allocator->getPcbPurchasingData() === null) {
                                            // this is for GUM-GS case
                                            /** @var SalesOrderDetailAllocatorManufactured $allocator */
                                            $command = new CreateStockItemOrderCommand($allocator->getStockCode(), $allocator->getVersion(), $allocator->getQtyToOrder(), $allocator->getPurchasingDataId(), $requirement->getId());
                                            $this->bus->handle($command);
                                        } else {
                                            foreach ($brdRequirements as $brdRequirement) {
                                                $this->handlePcbRequirement($brdRequirement, $allocator);
                                            }
                                        }
                                    }
                                }
                            }
                        }

                        break;
                    default:
                        throw new UnexpectedValueException("Unknown action '$action'");
                }
            }
        }
        return $qtyAllocated;
    }

    private function handlePcbRequirement(Requirement $requirement, SalesOrderDetailAllocatorManufactured $allocator)
    {
        if ($requirement->isPCB()) {
            if ($allocator instanceof SalesOrderDetailAllocatorManufactured){
                $command = new CreateStockItemOrderCommand($allocator->getStockCode(), $allocator->getVersion(), $allocator->getFabQtyToOrder(), $allocator->getPcbPurchasingDataId(), $requirement->getId());
                $this->bus->handle($command);
                // command bus flushes dbm
            }
        }
    }

    private function notifyOfAllocation(SalesOrder $order)
    {
        $event = new SalesOrderEvent($order);
        $event->disableEmail();
        $this->dispatchEvent(SalesEvents::ORDER_ALLOCATED, $event);
    }

    /**
     * @Route("/sales/order-item/{id}/allocations/where-to-build-report/",
     *   name="sales_orderitem_where_to_build_report")
     * @Template("sales/order/item/where-to-build-report.html.twig")
     */
    public function whereToBuildReportAction(SalesOrderDetail $lineItem, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::WAREHOUSE]);
        $userInput = $request->get("qtyToOrder");
        if ($userInput === null) {
            $userInputQtyToOrder = null;
        } else {
            $userInputQtyToOrder = intval($userInput);
        }

        if ($lineItem->isDummy()) {
            throw $this->badRequest("Cannot allocate stock for dummy items");
        } elseif ($lineItem->isCompleted()) {
            throw $this->badRequest("Line item is completed.");
        }

        $order = $lineItem->getSalesOrder();
        $whereToBuild = new WhereToBuild($lineItem, $this->dbm, $userInputQtyToOrder);

        return [
            'order' => $order,
            'item' => $lineItem->getStockItem(),
            'consumer' => $lineItem,
            'whereToBuild' => $whereToBuild,
        ];
    }

    /**
     * @Route("/sales/order-item/{id}/requirements/",
     *   name="sales_orderitem_clear_requirements")
     * @Method("DELETE")
     */
    public function clearRequirementsAction(SalesOrderDetail $lineItem)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::WAREHOUSE]);
        $lineItem->clearRequirements();
        $this->dbm->flush();
        $this->logNotice("Requirements regenerated.");
        return $this->redirectToRoute('sales_orderitem_allocate', [
            'id' => $lineItem->getId(),
        ]);
    }
}
