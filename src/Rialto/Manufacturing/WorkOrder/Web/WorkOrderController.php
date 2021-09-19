<?php

namespace Rialto\Manufacturing\WorkOrder\Web;

use FOS\RestBundle\View\View;
use JMS\JobQueueBundle\Entity\Job;
use JMS\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Rialto\Allocation\Allocation\AllocationFactory;
use Rialto\Allocation\Allocation\StockAllocation;
use Rialto\Allocation\Requirement\Requirement;
use Rialto\Database\Orm\EntityList;
use Rialto\Email\MailerInterface;
use Rialto\Manufacturing\Allocation\AllocatorIndex;
use Rialto\Manufacturing\Allocation\ChooseRequirementAllocation;
use Rialto\Manufacturing\Allocation\Command\AllocateCommand;
use Rialto\Manufacturing\Allocation\Orm\DQL\DqlStockAllocationRepository;
use Rialto\Manufacturing\Allocation\Orm\StockAllocationRepository;
use Rialto\Manufacturing\Allocation\RequirementAllocator;
use Rialto\Manufacturing\Allocation\WorkOrderAllocator;
use Rialto\Manufacturing\Allocation\WorkOrderAllocatorGroup;
use Rialto\Manufacturing\Requirement\RequirementFactory;
use Rialto\Manufacturing\Web\ManufacturingRouter;
use Rialto\Manufacturing\WorkOrder\Issue\WorkOrderIssuer;
use Rialto\Manufacturing\WorkOrder\WorkOrderAllocatorForChooseStock;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Manufacturing\WorkOrder\WorkOrderCollection;
use Rialto\Manufacturing\WorkOrder\WorkOrderCreation;
use Rialto\Manufacturing\WorkOrder\WorkOrderEmail;
use Rialto\Manufacturing\WorkOrder\WorkOrderFactory;
use Rialto\Manufacturing\WorkOrder\WorkOrderFamily;
use Rialto\Manufacturing\WorkOrder\WorkOrderPdfGenerator;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Purchasing\Producer\StockProducerFactory;
use Rialto\ResourceException;

use Rialto\Security\Role\Role;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\ItemCanBeBuilt;
use Rialto\Stock\Item\ManufacturedStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\VersionException;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\Response\PdfResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Controller for creating, editing, and managing work orders.
 */
class WorkOrderController extends RialtoController
{
    /**
     * @var ManufacturingRouter
     */
    private $router;

    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var EngineInterface
     */
    private $templating;

    /**
     * @var WorkOrderPdfGenerator
     */
    private $pdfGenerator;

    /**
     * @var WorkOrderFactory
     */
    private $woFactory;

    /**
     * @var RequirementFactory
     */
    private $reqFactory;

    /**
     * @var DqlStockAllocationRepository
     */
    private $stockAllocationRepo;

    /** @var SerializerInterface */
    private $serializer;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    protected function init(ContainerInterface $container)
    {
        $this->router = $this->get(ManufacturingRouter::class);
        $this->validator = $this->get(ValidatorInterface::class);
        $this->templating = $this->getTemplating();
        $this->pdfGenerator = $this->get(WorkOrderPdfGenerator::class);
        $this->woFactory = $this->get(WorkOrderFactory::class);
        $this->reqFactory = $this->get(RequirementFactory::class);
        $this->stockAllocationRepo = $this->get(StockAllocationRepository::class);
        $this->serializer = $this->get(SerializerInterface::class);
    }

    /**
     * @Route("/manufacturing/workorder/", name="workorder_list")
     * @Route("/api/v2/manufacturing/workorder/")
     * @Method("GET")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::MANUFACTURING]);
        $form = $this->createForm(ListFilterType::class);
        $form->submit($request->query->all());
        $repo = $this->getRepository(WorkOrder::class);
        $list = new EntityList($repo, $form->getData());
        return View::create(WorkOrderSummary::fromList($list))
            ->setHeader('record-count', $list->total())
            ->setTemplate("manufacturing/order/list.html.twig")
            ->setTemplateData([
                'form' => $form->createView(),
                'list' => $list,
            ]);
    }

    /**
     * @Route("/manufacturing/workorder/{order}/", name="work_order_view", options={"expose": true})
     * @Method("GET")
     * @Template("manufacturing/order/view.html.twig")
     */
    public function viewAction(WorkOrder $order)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        return ['entity' => $order];
    }

    /**
     * @Route("/Manufacturing/WorkOrder/", name="work_order_create")
     * @Template("manufacturing/order/create.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::MANUFACTURING);
        $sku = $request->get('stockItem');
        if (!$sku) {
            return $this->render('manufacturing/order/select-item.html.twig');
        }

        /* @var $stockItem ManufacturedStockItem */
        $stockItem = $this->needEntity(StockItem::class, $sku);
        if (!$this->itemCanBeBuilt($stockItem)) {
            return $this->redirectToRoute('stock_item_view', [
                'item' => $stockItem->getId(),
            ]);
        }

        $creation = new WorkOrderCreation($stockItem);
        $creation->loadDefaultValues($this->dbm);
        if ($request->get('qtyOrdered')) {
            $creation->setQtyOrdered($request->get('qtyOrdered'));
        }
        $form = $this->createForm(WorkOrderCreationType::class, $creation);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $workOrder = $this->woFactory->create($creation);
                $this->dbm->persist($workOrder);
                $this->dbm->flushAndCommit();
                $this->logNotice("Created $workOrder.");
                return $this->redirectToWorkOrder($workOrder);
            } catch (ResourceException $ex) {
                $this->dbm->rollBack();
                $this->logException($ex);
            } catch (VersionException $ex) {
                $this->dbm->rollBack();
                $this->logException($ex);
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }

        return [
            'form' => $form->createView(),
            'item' => $stockItem,
            'creation' => $creation,
        ];
    }

    /** @return bool */
    private function itemCanBeBuilt(StockItem $item)
    {
        $constraint = new ItemCanBeBuilt();
        $errors = $this->validator->validate($item, $constraint);
        if (count($errors) > 0) {
            $this->logErrors($errors);
            return false;
        }
        return true;
    }

    private function redirectToWorkOrder(WorkOrder $order)
    {
        $url = $this->router->workOrderView($order);
        return $this->redirect($url);
    }

    /**
     * @Route("/Manufacturing/WorkOrder/{id}/requirements", name="work_order_requirements")
     * @Method("POST")
     */
    public function requirementsAction(WorkOrder $order)
    {
        $this->denyAccessUnlessGranted(Role::MANUFACTURING);
        if ($order->isIssued()) {
            throw $this->badRequest("Cannot regenerate requirements for an issued order.");
        }
        if (!$order->bomExists()) {
            $sku = $order->getFullSku();
            $this->logError("$sku has no BOM");
            return $this->redirectToRoute('stock_item_view', [
                'item' => $order->getSku()
            ]);
        }
        if ($order->getPurchasingData()->isTurnkey()) {
            $this->logError("$order is a turnkey build");
            return $this->redirectToWorkOrder($order);
        } else {
            $this->dbm->beginTransaction();
            try {
                $this->reqFactory->updateRequirements($order);
                $this->dbm->flushAndCommit();
                $this->logNotice("Requirements updated successfully.");
                return $this->redirectToWorkOrder($order);
            } catch (VersionException $ex) {
                $this->dbm->rollBack();
                $this->logException($ex);
                return $this->redirectToWorkOrder($order);
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }
    }


    /**
     * Download the build instructions documentation.
     *
     * @Route("/Manufacturing/WorkOrder/{id}/instructions", name="Manufacturing_WorkOrder_instructions")
     */
    public function instructionsAction(WorkOrder $wo)
    {
        $this->denyAccessUnlessGranted([Role::MANUFACTURING, Role::SUPPLIER_SIMPLE]);
        if (!$wo->bomExists()) {
            $this->logError(sprintf(
                'The bill of materials does not exist yet for %s',
                $wo->getFullSku()
            ));
            return $this->redirectToRoute('item_version_edit', [
                'item' => $wo->getSku(),
                'version' => $wo->getVersion(),
            ]);
        }

        $pdfData = $this->pdfGenerator->getPdf($wo);
        $this->dbm->flush();
        $filename = sprintf('%s.pdf', $wo->getFullSku());
        return PdfResponse::create($pdfData, $filename);
    }

    /**
     * Allocate parts to a purchase order.
     *
     * @Route("/manufacturing/purchaseorder/{id}/allocate/",
     *   name="purchase_order_allocate")
     * @Template("manufacturing/order/allocate.html.twig")
     */
    public function allocateAction(PurchaseOrder $po, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::MANUFACTURING);
        if (!$po->hasWorkOrders()) {
            throw $this->notFound("$po has no work orders");
        }
        if ($po->isCompleted()) {
            throw $this->badRequest("$po is completed");
        }

        $list = WorkOrderCollection::fromPurchaseOrder($po);
        $cm = $po->getBuildLocation();

        $allocator = new WorkOrderAllocator($list);

        if ($request->get('fromCM') && (!$cm->isHeadquarters())) {
            $allocator->addLocation($cm);
            $allocator->setShareBins(true);
        }
        $allocator->addLocation($this->getHeadquarters());
        $allocator->createItems($this->dbm);
        $allocator->loadPurchasingData($this->dbm);
        $allocator->validate($this->validator);

        $index = new AllocatorIndex($allocator);
        $canOrder = $this->isGranted(Role::PURCHASING);
        $index->setCanOrder($canOrder);

        $otherWorkOrdersWithDateAndStockAllocations = $this->stockAllocationRepo
            ->getOtherWorkOrdersWithDateAndStockAllocations($allocator);

        $orderForm = $this->createOrderForm($index);

        $chooseOtherAllocationsForm = $this->createForm(ChooseOtherAllocationsType::class);

        if ($request->isMethod('POST')) {
            if ($request->get('allocate')) {
                /** @var $allocFactory AllocationFactory */
                $allocFactory = $this->get(AllocationFactory::class);
                $this->dbm->beginTransaction();
                try {
                    $qtyAllocated = $allocator->allocate($allocFactory);
                    $this->dbm->flushAndCommit();
                    $this->logNotice(sprintf(
                        'Allocated %s units.', number_format($qtyAllocated)
                    ));
                    return $this->redirect($this->getCurrentUri());
                } catch (\Exception $ex) {
                    $this->dbm->rollBack();
                    throw $ex;
                }
            } elseif ($request->get('order') && $canOrder) {
                $orderForm->handleRequest($request);
                if ($orderForm->isValid()) {
                    /** @var $factory StockProducerFactory */
                    $factory = $this->get(StockProducerFactory::class);
                    $toOrder = $index->getGroupToOrder();
                    $this->dbm->beginTransaction();
                    try {
                        foreach ($toOrder->getItems() as $item) {
                            $producer = $item->orderStock($factory);
                            $this->logItemOrdered($item, $producer);
                        }
                        $this->dbm->flushAndCommit();
                        return $this->redirect($this->getCurrentUri());
                    } catch (VersionException $ex) {
                        $this->logException($ex);
                        $this->dbm->rollBack();
                    } catch (\Exception $ex) {
                        $this->dbm->rollBack();
                        throw $ex;
                    }
                }
            }
        }

        $allocGroup = $this->serializeGroup($index->getGroupToOrder());

        return [
            'po' => $po,
            'list' => $list,
            'location' => $cm,
            'index' => $index,
            'orderForm' => $orderForm->createView(),
            'mappedOtherWorkOrders' => $otherWorkOrdersWithDateAndStockAllocations,
            'chooseOtherAllocationForm' => $chooseOtherAllocationsForm,
            'allocGroup' => $allocGroup,
        ];
    }

    /**
     * Choose and delete Stock Allocation on large scale
     *
     * @Route("/manufacturing/purchaseorder/{id}/choosestockallocation",
     *   name="purchase_order_choose_stock_allocation")
     * @Template("manufacturing/order/choose-stock-allocations.html.twig")
     */
    public function chooseStockAllocationAction(PurchaseOrder $po, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::MANUFACTURING);
        if (!$po->hasWorkOrders()) {
            throw $this->notFound("$po has no work orders");
        }
        if ($po->isCompleted()) {
            throw $this->badRequest("$po is completed");
        }

        $list = WorkOrderCollection::fromPurchaseOrder($po);
        $allocator = new WorkOrderAllocatorForChooseStock($list);

        $cm = $po->getBuildLocation();

        if ($request->get('fromCM') && (!$cm->isHeadquarters())) {
            $allocator->addLocation($cm);
            $allocator->setShareBins(true);
        }
        $allocator->addLocation($this->getHeadquarters());
        $allocator->createItems($this->dbm);
        $allocator->loadPurchasingData($this->dbm);
        $allocator->validate($this->validator);

        $index = new ChooseRequirementAllocation($allocator);

        /** @var StockAllocation[] $allocations */
        $allocations = $index->getStockAllocations();
        $form = $this->createForm(StockAllocationSelectAndDeleteType::class, null, ['allocations' => $allocations]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $deallocs = $form->getData()['stockAllocations'];
            $this->dbm->beginTransaction();
            try {
                foreach ($deallocs as $alloc) {
                    /** @var StockAllocation $alloc */
                    $alloc->close();
                }
                $this->dbm->flushAndCommit();
                $this->logNotice(sprintf(
                    'Deallocated parts.'
                ));
                return $this->redirect($this->getCurrentUri());
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }

        return [
            'po' => $po,
            'list' => $list,
            'location' => $cm,
            'allocations' => $allocations,
            'form' => $form->createView(),
        ];
    }


    /**
     * @Route("/manufacturing/purchaseorder/{id}/allocate/steal/",
     *   name="purchase_order_allocate_steal", options={"expose": true})
     * @Method("POST")
     */
    public function chooseAllocationAction(PurchaseOrder $order, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::MANUFACTURING]);

        $selected = $request->get('selected', []);
        $ordered = $request->get('ordered', []);

        /** @var RequirementAllocator[] $requirements */
        $requirements = [];
        $workOrders = $order->getWorkOrders();
        foreach ($workOrders as $workOrder) {
            foreach ($workOrder->getRequirements() as $woReq) {
                $sku = $woReq->getFullSku();
                if (empty($requirements[$sku])) {
                    $requirements[$sku] = new RequirementAllocator();
                }
                $requirements[$sku]->addRequirement($woReq);
            }
        }

        $repo = $this->dbm->getRepository(StockAllocation::class);

        /** @var $factory StockProducerFactory */
        $factory = $this->get(StockProducerFactory::class);
        $this->dbm->beginTransaction();
        try {
            foreach ($ordered as $orderRec) {
                $sku = $orderRec['sku'];
                $qty = $orderRec['qty'];
                $allocator = $requirements[$sku];
                if ($qty > 0) {
                    $producer = $factory->create($allocator, $qty);
                    $this->dbm->persist($producer);
                    foreach ($allocator->getRequirements() as $requirement) {
                        $alloc = $requirement->createAllocation($producer);
                        $alloc->adjustQuantity($requirement->getTotalQtyUnallocated());
                        if ($alloc->getQtyAllocated() != 0) {
                            $this->dbm->persist($alloc);
                        }
                    }
                }
            }
            /** @var StockAllocation[] $allocations */
            $allocations = $repo->findBy([
                'id' => $selected,
            ]);

            foreach ($allocations as $allocation) {
                $this->dbm->delete(StockAllocation::class, $allocation);
            }

//            $allocate = new Job(AllocateCommand::NAME, [$order->getId(), '--force']);
//            $this->dbm->persist($allocate);
            $this->dbm->flushAndCommit();
            $this->logger->notice("There is no auto allocator, please use manual allocate command on Allocate From User's Sources Configuration page.");
            return $this->redirect($this->getCurrentUri());
        } catch (VersionException $ex) {
            $this->logException($ex);
            $this->dbm->rollBack();
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }

        return View::create();
    }

    /**
     * @param WorkOrderAllocatorGroup $group
     * @return string[]
     */
    private function serializeGroup(WorkOrderAllocatorGroup $group)
    {
        $locations = $group->getLocations();
        $warehouse = null;
        $cm = null;
        foreach ($locations as $location) {
            if ($location->getId() == Facility::HEADQUARTERS_ID) {
                $warehouse = $location;
            } else {
                $cm = $location;
            }
        }
        return array_map(function (RequirementAllocator $alloc) use ($warehouse, $cm) {
            $requirements = array_map(function (Requirement $requirement) {
                if ($requirement->getConsumer() !== null) {
                    /** @var WorkOrder $workOrder */
                    $workOrder = $requirement->getConsumer();
                    return ['reqId' => $requirement->getId(),
                        'woId'=> $workOrder->getId()];
                } else {
                    return ['reqId' => $requirement->getId(),
                            'woId'=> 0];
                }
            }, $alloc->getRequirements());
            $toOrder = max($alloc->getQtyToOrder(), $alloc->getEoq());
            return [
                'fullSku' => $alloc->getFullSku(),
                'description' => $alloc->getDescription(),
                'qtyNeeded' => $alloc->getQtyNeeded(),
                'qtyAllocated' => $alloc->getQtyAllocated(),
                'qtyStillNeeded' => $alloc->getQtyStillNeeded(),
                'totalAtCm' =>  $cm ? $alloc->getTotalQtyAt($cm) : 0,
                'availableAtCm' => $cm ? $alloc->getQtyAvailableAt($cm) : 0,
                'toAllocateFromCm' => $cm ? $alloc->getQtyToAllocateFrom($cm) : 0,
                'totalAtWarehouse' => $warehouse ? $alloc->getTotalQtyAt($warehouse) : 0,
                'availableAtWarehouse' => $warehouse ? $alloc->getQtyAvailableAt($warehouse) : 0,
                'toAllocateFromWarehouse' => $warehouse ? $alloc->getQtyToAllocateFrom($warehouse) : 0,
                'totalQtyOnOrder' => $alloc->getTotalQtyOnOrder(),
                'availableQtyOnOrder' => $alloc->getQtyAvailableOnOrder(),
                'qtyToAllocateFromOrders' => $alloc->getQtyToAllocateFromOrders(),
                'qtyToOrder' => $toOrder,
                'supplierName' => $alloc->getSupplier() ? $alloc->getSupplier()->getName() : '',
                'requirements' => $requirements,
            ];
        }, $group->getItems());
    }


    /**
     * @deprecated Use allocateAction() instead
     *
     * @Route("/manufacturing/workorder/{id}/allocate", name="work_order_allocate")
     * @Route("/Manufacturing/WorkOrder/{id}/allocate")
     */
    public function allocateWorkOrderAction(WorkOrder $wo, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::MANUFACTURING);
        $po = $wo->getPurchaseOrder();
        return $this->redirectToRoute('purchase_order_allocate', [
            'id' => $po->getId(),
            'fromCM' => $request->get('fromCM'),
        ]);
    }

    private function createOrderForm(AllocatorIndex $index)
    {
        $toOrder = $index->getGroupToOrder();

        $form = $this->createFormBuilder($toOrder)
            ->add('items', CollectionType::class, [
                'entry_type' => RequirementAllocatorType::class,
                'label' => false,
            ])
            ->getForm();
        return $form;
    }

    private function logItemOrdered(RequirementAllocator $item, StockProducer $producer = null)
    {
        if (!$producer) {
            return;
        }
        $msg = sprintf('%s units of %s added to %s.',
            number_format($item->getQtyToOrder()),
            $producer->getSku(),
            $producer->getSourceDescription()
        );
        $this->logNotice($msg);
    }

    /**
     * @Route("/Manufacturing/WorkOrder/{id}/issues/", name="Manufacturing_WorkOrder_issue")
     * @Template("manufacturing/order/issue.html.twig")
     */
    public function issueAction(WorkOrder $wo, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::MANUFACTURING);
        $status = $wo->getAllocationStatus();
        $maxPossible = $status->getQtyAtLocation() - $status->getQtyDelivered();
        /** @var $form FormInterface */
        $form = $this->createFormBuilder()
            ->setAction($this->getCurrentUri())
            ->add('qtyToIssue', IntegerType::class, [
                'label' => 'Quantity to issue',
                'data' => $maxPossible,
                'constraints' => new Assert\Range([
                    'min' => 1,
                    'max' => $maxPossible,
                    'maxMessage' => "workorder.issue_limit",
                ]),
            ])
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $issuer = $this->get(WorkOrderIssuer::class);
            /* @var $issuer WorkOrderIssuer */
            $data = $form->getData();
            $this->dbm->beginTransaction();
            try {
                $issue = $issuer->issue($wo, $data['qtyToIssue']);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $this->logNotice(sprintf("Issued %s units of %s.",
                number_format($issue->getQtyIssued()),
                $wo
            ));
            $uri = $this->router->workOrderView($wo);
            return JsonResponse::javascriptRedirect($uri);
        }

        return [
            'workOrder' => $wo,
            'status' => $status,
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
        ];
    }


    /**
     * @Route("/Manufacturing/WorkOrder/{id}/send", name="Manufacturing_WorkOrder_send")
     * @Template("manufacturing/order/send.html.twig")
     */
    public function sendAction(WorkOrder $wo, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::MANUFACTURING);
        $sender = $this->getCurrentUser();
        $email = new WorkOrderEmail($wo, $sender);
        $email->render($this->templating);

        $form = $this->createForm(WorkOrderEmailType::class, $email);
        $returnUri = $this->router->workOrderView($wo);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email->attachBuildInstructions($this->pdfGenerator);
            $mailer = $this->get(MailerInterface::class);
            $mailer->send($email);
            $wo->setSent($this->getCurrentUser(), 'emailed work order');
            $this->dbm->flush();
            $this->logNotice('Email sent successfully.');
            return $this->redirect($returnUri);
        }

        return [
            'wo' => $wo,
            'form' => $form->createView(),
            'cancelUri' => $returnUri,
        ];
    }

    /**
     * @Route("/Manufacturing/WorkOrder/{id}/openForAllocation", name="Manufacturing_WorkOrder_openForAllocation")
     * @Method("PUT")
     */
    public function openForAllocationAction(WorkOrder $wo, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::MANUFACTURING);
        $openForAlloc = (bool) $request->get("openForAllocation");
        $this->dbm->beginTransaction();
        try {
            $wo->setOpenForAllocation($openForAlloc);
            $this->dbm->flushAndCommit();
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }

        return new Response('OK');
    }

    /**
     * @Route("/Manufacturing/WorkOrder/{id}/components", name="manufacturing_workorder_components")
     * @Template("manufacturing/order/components.html.twig")
     */
    public function componentsAction(WorkOrder $wo, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::MANUFACTURING, Role::WAREHOUSE]);
        if ($request->get('format') == 'csv') {
            $csv = ComponentCsvFile::create($wo);
            $filename = ComponentCsvFile::getFilename($wo);
            return FileResponse::fromData($csv->toString(), $filename, 'text/csv');
        }

        $cancelUri = $this->getReturnUri($this->generateUrl('warehouse_dashboard'));
        return [
            'po' => $wo->getPurchaseOrder(),
            'workOrders' => new WorkOrderFamily($wo),
            'cancelUri' => $cancelUri,
        ];
    }

}
