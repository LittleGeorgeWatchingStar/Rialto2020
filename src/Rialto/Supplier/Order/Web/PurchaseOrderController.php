<?php

namespace Rialto\Supplier\Order\Web;

use FOS\RestBundle\View\View;
use Exception;
use Gumstix\Storage\FileStorage;
use Gumstix\Storage\StorageException;
use Rialto\Allocation\Requirement\RequirementTask\RequirementTaskFactory;
use Rialto\Cms\CmsEngine;
use Rialto\Email\Email;
use Rialto\Email\Mailable\Web\MailableType;
use Rialto\Email\Mailable\Web\TextMailableType;
use Rialto\Email\MailerInterface;
use Rialto\Exception\InvalidDataException;
use Rialto\Manufacturing\Audit\AuditAdjuster;
use Rialto\Manufacturing\Audit\PurchaseOrderAudit;
use Rialto\Manufacturing\ClearToBuild\ClearToBuildFactory;
use Rialto\Manufacturing\PurchaseOrder\OrderStatusIndex;
use Rialto\Manufacturing\WorkOrder\Issue\WorkOrderIssuer;
use Rialto\Manufacturing\WorkOrder\WorkOrderCollection;
use Rialto\Panelization\IO\PanelizationStorage;
use Rialto\Panelization\Panelizer;
use Rialto\Purchasing\Invoice\Orm\SupplierInvoiceRepository;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Purchasing\Order\Attachment\PurchaseOrderAttachmentLocator;
use Rialto\Purchasing\Order\Orm\PurchaseOrderRepository;
use Rialto\Purchasing\Order\POBuildFiles;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Order\PurchaseOrderEvent;
use Rialto\Purchasing\Producer\StockProducerEvent;
use Rialto\Purchasing\PurchasingEvents;
use Rialto\Purchasing\Receiving\Receiver;
use Rialto\Purchasing\Receiving\Web\GoodsReceived;
use Rialto\Purchasing\Receiving\Web\GoodsReceivedType;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\Role\Role;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Facility\Orm\FacilityRepository;
use Rialto\Stock\Level\StockLevelService;
use Rialto\Stock\Transfer\Orm\TransferRepository;
use Rialto\Stock\Transfer\Transfer;
use Rialto\Supplier\Order\Email\RequestToScrapEmail;
use Rialto\Supplier\Order\Web\Facades\OrderStatusIndexFacade;
use Rialto\Supplier\Order\Web\Facades\PurchaseOrderFacade;
use Rialto\Supplier\Order\Web\TrackingFacades\SupplierInvoiceTrackingFacadesFactory;
use Rialto\Supplier\Order\Web\TrackingFacades\TrackingFacade;
use Rialto\Supplier\SupplierEvents;
use Rialto\Supplier\Web\SupplierController;
use Rialto\Time\Web\DateType;
use Rialto\Web\Response\FileResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Templating\EngineInterface;
use Twig\Environment;
use Twig_Environment;

/**
 * Allows suppliers to manage their purchase orders.
 *
 * @Route("/supplier")
 */
class PurchaseOrderController extends SupplierController
{
    /** @var PurchaseOrderRepository */
    private $orders;

    /** @var FacilityRepository */
    private $locations;

    /** @var TransferRepository */
    private $transfers;

    /** @var AuditAdjuster */
    private $adjuster;

    /** @var WorkOrderIssuer */
    private $issuer;

    /** @var Environment */
    private $twig;

    /** @var RequirementTaskFactory */
    private $factory;

    /** @var ClearToBuildFactory */
    private $clearToBuild;

    /** @var SupplierInvoiceRepository */
    private $supplierInvoiceRepo;

    /**
     * Initialize any additional properties that the controller needs.
     */
    protected function init(ContainerInterface $container)
    {
        $this->orders = $this->dbm->getRepository(PurchaseOrder::class);
        $this->locations = $this->dbm->getRepository(Facility::class);
        $this->transfers = $this->dbm->getRepository(Transfer::class);
        $this->adjuster = $this->get(AuditAdjuster::class);
        $this->issuer = $this->get(WorkOrderIssuer::class);
        $this->twig = $this->get(Twig_Environment::class);
        $this->factory = $this->get(RequirementTaskFactory::class);
        $this->clearToBuild = $this->get(ClearToBuildFactory::class);
        $this->supplierInvoiceRepo = $this->dbm->getRepository(SupplierInvoice::class);
    }

    /**
     * @Route("/{id}/order/", name="supplier_order_list")
     * @Route("/{id}/dashboard/")
     * @Method("GET")
     * @Template("supplier/purchaseOrder/list.html.twig")
     */
    public function listAction(Supplier $supplier, Request $request)
    {
        $this->checkDashboardAccess($supplier);
        $this->setReturnUri();

        $location = $this->locations->findBySupplier($supplier);
        if (!$location) {
            throw $this->notFound();
        }
        $session = $request->getSession();
        $rework = (bool) $request->get('rework', $session->get('dashboard_rework'));
        $session->set('dashboard_rework', $rework);
        return [
            'supplier' => $supplier,
            'activeTab' => $rework ? 'rework' : 'build',

        ];
    }

    /**
     * This is a iframe source for @see listAction()
     * the template @see listAction() extends uses dojo
     * we use iframe so that react does not conflict with dojo
     *
     * @Route("/{id}/order/table/", name="supplier_order_list_table")
     * @Method("GET")
     * @Template("supplier/purchaseOrder/table.html.twig")
     */
    public function tableAction(Supplier $supplier, Request $request)
    {
        $this->checkDashboardAccess($supplier);
        $this->setReturnUri();

        $location = $this->locations->findBySupplier($supplier);
        if (!$location) {
            throw $this->notFound();
        }

        $session = $request->getSession();
        $rework = (bool) $request->get('rework', $session->get('dashboard_rework'));
        $session->set('dashboard_rework', $rework);
        /** @var PurchaseOrder[] $purchOrders */
        $purchOrders = $this->orders->createBuilder()
            ->bySupplier($supplier)
            ->isOpen()
            ->byRework($rework)
            ->prefetchTasks()
            ->orderByRequestedDate()
            ->getResult();

        $index = new OrderStatusIndex($purchOrders);

        $encoders = [new JsonEncoder()];
        $normalizer = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizer, $encoders);
        $orderStatusIndexFacade = new OrderStatusIndexFacade($index, $this->factory, $this->clearToBuild, $this->twig);

        $json = $serializer->serialize($orderStatusIndexFacade, 'json');

        return [
            'supplier' => $supplier,
            'orders' => $index,
            'activeTab' => $rework ? 'rework' : 'build',
            'json'=> $json,
        ];
    }

    /**
     * @Route("/{id}/order/table/facade/{facade}", name="supplier_order_facade", options={"expose": true})
     */
    public function facadeAction(Supplier $supplier, $facade, Request $request)
    {
        $session = $request->getSession();
        $rework = (bool) $request->get('rework', $session->get('dashboard_rework'));
        $session->set('dashboard_rework', $rework);

        $purchOrders = $this->orders->createBuilder()
            ->bySupplier($supplier)
            ->isOpen()
            ->byRework($rework)
            ->prefetchTasks()
            ->orderByRequestedDate()
            ->getResult();
        $index = new OrderStatusIndex($purchOrders);
        $orders = iterator_to_array($index->getIterator())[$facade];
        $facades = array_map(function (PurchaseOrder $po) {
            return new PurchaseOrderFacade($po, $this->factory, $this->clearToBuild, $this->twig);
        }, $orders);
        return View::create($facades);
    }

    /**
     * Allows the supplier to view the engineering documentation for the
     * PO and approve or reject it.
     *
     * @Route("/purchaseorder/{id}/approve/", name="supplier_order_approve")
     * @Template("supplier/purchaseOrder/approve.html.twig")
     */
    public function approveAction(PurchaseOrder $po, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_SIMPLE, Role::EMPLOYEE]);
        $supplier = $po->getSupplier();
        $this->checkDashboardAccess($supplier);

        $form = $this->createForm(PurchaseOrderApprovalType::class, $po);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->flush();
            return $this->dialogRedirect($supplier);
        }

        /** @var $locator PurchaseOrderAttachmentLocator */
        $locator = $this->get(PurchaseOrderAttachmentLocator::class);
        $panelFiles = $po->isInitiatedBy(Panelizer::INITIATOR_CODE)
            ? $locator->getPanelizationFiles($po)
            : null;

        return [
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
            'cancelUri' => $this->getDashboardUri($supplier),
            'buildFilesList' => $locator->getBuildFilesForPurchaseOrder($po),
            'panelFiles' => $panelFiles,
            'po' => $po,
        ];
    }

    /**
     * @Route("/purchaseOrder/{id}/panelFile/{filename}",
     *   name="supplier_panel_file")
     */
    public function panelFileAction(PurchaseOrder $po, $filename)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_SIMPLE, Role::EMPLOYEE]);
        try {
            /** @var $storage PanelizationStorage */
            $storage = $this->get(PanelizationStorage::class);
            $data = $storage->getFileContents($po, $filename);
            $type = $storage->getMimeType($po, $filename);
        } catch (StorageException $ex) {
            $storage = POBuildFiles::create($po, $this->get(FileStorage::class));
            $data = $storage->getContents($filename);
            $type = $storage->getMimeType($filename);
        }
        return FileResponse::fromData($data, $filename, $type);
    }

    /**
     * Attach the supplier's internal reference number (such as an RMA number)
     * to the PO.
     *
     * @Route("/purchaseOrder/{id}/supplierReference", name="supplier_order_reference")
     * @Template("supplier/purchaseOrder/supplierReference.html.twig")
     */
    public function supplierReferenceAction(PurchaseOrder $po, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_SIMPLE, Role::EMPLOYEE]);
        $supplier = $po->getSupplier();
        $this->checkDashboardAccess($supplier);

        $form = $this->createFormBuilder($po)
            ->add('supplierReference', TextType::class, [
                'label' => sprintf('Enter your %s RMA number', $supplier->getName()),
            ])
            ->add('submit', SubmitType::class)
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->flush();
            $event = new PurchaseOrderEvent($po);
            $this->dispatchEvent(SupplierEvents::SUPPLIER_REFERENCE, $event);
            return $this->dialogRedirect($supplier);
        }

        return [
            'form' => $form->createView(),
            'cancelUri' => $this->getDashboardUri($supplier),
        ];
    }

    /**
     * When components have been shipped directly to the CM on multiple POs,
     * use this action to select which incoming PO to receive.
     *
     * @Route("/purchaseorder/{id}/incoming/", name="supplier_incoming_select")
     * @Template("supplier/purchaseOrder/selectIncoming.html.twig")
     */
    public function selectIncomingAction(PurchaseOrder $po)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_ADVANCED, Role::EMPLOYEE]);
        $supplier = $po->getSupplier();
        $this->checkDashboardAccess($supplier);

        $family = WorkOrderCollection::fromPurchaseOrder($po);
        $cm = $po->getBuildLocation();
        $incoming = $family->getOutstandingPOs($cm);

        return [
            'orders' => $incoming,
            'company' => $this->getDefaultCompany(),
            'cm' => $cm,
            'cancelUri' => $this->getDashboardUri($supplier),
        ];
    }

    /**
     * Receive a purchase order for components that has been shipped directly
     * to the CM.
     *
     * @Route("/incoming/{id}/receive/", name="supplier_incoming_receive")
     * @Template("supplier/purchaseOrder/receiveIncoming.html.twig")
     */
    public function receiveIncomingAction(PurchaseOrder $po, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_ADVANCED, Role::EMPLOYEE]);
        if (!$po->isSent()) {
            throw $this->badRequest(
                "Cannot receive a purchase order that has not been sent.");
        }
        $deliveryLocation = $po->getDeliveryLocation();
        $supplier = $deliveryLocation->getSupplier();
        $this->checkDashboardAccess($supplier);

        $template = new GoodsReceived($po);
        $form = $this->createForm(GoodsReceivedType::class, $template);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $grn = $template->create($this->getCurrentUser());
                $this->dbm->persist($grn);
                $this->dbm->flush();

                $returnUri = $this->generateUrl('supplier_grn_view', [
                    'id' => $grn->getId(),
                ]);

                $receiver = $this->get(Receiver::class);
                $receiver->receive($grn);
                $this->dbm->flushAndCommit();
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            return $this->redirect($returnUri);
        }

        return [
            'po' => $po,
            'company' => $this->getDefaultCompany(),
            'cm' => $deliveryLocation,
            'form' => $form->createView(),
            'cancelUri' => $this->getDashboardUri($supplier),
        ];
    }

    /**
     * Enter the commitment date when a PO is expected to be delivered.
     *
     * @Route("/purchaseOrder/{id}/commitment", name="supplier_order_commitment")
     * @Template("supplier/purchaseOrder/commitmentDate.html.twig")
     */
    public function commitmentDateAction(PurchaseOrder $po, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_SIMPLE, Role::EMPLOYEE]);
        $supplier = $po->getSupplier();
        $this->checkDashboardAccess($supplier);

        $container = new stdClass();
        $container->items = [];
        foreach ($po->getItems() as $item) {
            if ($item->isWorkOrder() && $item->hasParent()) {
                // commitment dates automatically cascade to child WOs.
                continue;
            }
            $id = $item->getId();
            $container->items[$id] = $item;
            $item->initializeCommitmentDate();
        }

        $form = $this->createFormBuilder($container)
            ->add('items', CollectionType::class, [
                'entry_type' => CommitmentDateType::class,
                'entry_options' => ['error_bubbling' => true],
                'error_bubbling' => true,
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->flush();
            foreach ($container->items as $poItem) {
                $event = new StockProducerEvent($poItem);
                $this->dispatchEvent(SupplierEvents::COMMITMENT_DATE, $event);
            }
            return $this->dialogRedirect($supplier);
        }

        return [
            'po' => $po,
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
            'cancelUri' => $this->getDashboardUri($supplier),
        ];
    }

    /**
     * Display known shortages to the user and give them the opportunity to
     * correct any mistakes.
     *
     * @Route("/purchaseOrder/{orderId}/shortages/", name="supplier_shortages")
     * @Method("GET")
     * @Template("supplier/purchaseOrder/shortages.html.twig")
     */
    public function shortagesAction($orderId)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_SIMPLE, Role::EMPLOYEE]);
        $po = $this->orders->deepFetch($orderId);
        $supplier = $po->getSupplier();
        $this->checkDashboardAccess($supplier);

        $audit = new PurchaseOrderAudit($po, $this->dispatcher());
        return [
            'po' => $po,
            'audit' => $audit,
            'transfers' => $this->findUnreceivedTransfers($po),
            'cancelUri' => $this->getOrderListUri($supplier),
        ];
    }

    private function findUnreceivedTransfers(PurchaseOrder $po)
    {
        return $this->transfers->createBuilder()
            ->forPurchaseOrder($po)
            ->sent()
            ->notReceived()
            ->orderById()
            ->getResult();
    }

    /**
     * Ensure that the supplier has all of the components required to
     * build the PO.
     *
     * @Route("/purchaseOrder/{id}/audit/", name="supplier_auditBuild")
     * @Template("supplier/purchaseOrder/audit.html.twig")
     */
    public function auditAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_SIMPLE, Role::EMPLOYEE]);
        $po = $this->orders->deepFetch($id);
        $supplier = $po->getSupplier();
        $this->checkDashboardAccess($supplier);

        $audit = new PurchaseOrderAudit($po, $this->dispatcher());
        $form = $this->createForm(PurchaseOrderAuditType::class, $audit);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $audit->adjustAllocations($this->adjuster);
                $audit->sendNotifications();
                $this->dbm->flushAndCommit();
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            if (!$this->logWarnings($audit->getWarnings())) {
                $this->logNotice("Successfully updated shortages for $po.");
            }

            if ($request->request->get('issue') &&
                $this->isGranted([Role::STOCK, Role::SUPPLIER_ADVANCED])) {
                if ($audit->childrenAreKitComplete()) {
                    $this->dbm->beginTransaction();
                    try {
                        $audit->issueChildOrders($this->issuer);
                        $this->dbm->flushAndCommit();
                    } catch (Exception $ex) {
                        $this->dbm->rollBack();
                        throw $ex;
                    }
                } else {
                    $msg = "$po cannot be issued because it is not kit-complete.";
                    $this->logError(ucfirst($msg));
                }
            }

            return $this->redirectToRoute('supplier_shortages', [
                'orderId' => $po->getId(),
            ]);
        }

        return [
            'po' => $po,
            'audit' => $audit,
            'transfers' => $this->findUnreceivedTransfers($po),
            'location' => $supplier->getFacility(),
            'mainForm' => $form->createView(),
            'cancelUri' => $this->getOrderListUri($supplier),
        ];
    }

    /**
     * Show all components required for the work order.
     *
     * @Route("/purchaseorder/{id}/components/", name="supplier_order_components")
     * @Template("supplier/purchaseOrder/components.html.twig")
     */
    public function componentsAction(PurchaseOrder $po, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_SIMPLE, Role::EMPLOYEE]);
        $supplier = $po->getSupplier();
        $this->checkDashboardAccess($supplier);

        return [
            'po' => $po,
            'workOrders' => $po->getWorkOrders(),
            'cancelUri' => $this->getDashboardUri($supplier),
        ];
    }

    /**
     * Notify us that the unreceived quantity cannot or will not be built
     * and should be scrapped.
     *
     * @Route("/purchaseorder/{id}/scrap/", name="supplier_order_scrap")
     * @Template("supplier/purchaseOrder/requestToScrap.html.twig")
     */
    public function requestToScrapAction(PurchaseOrder $po, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_SIMPLE, Role::EMPLOYEE]);
        $supplier = $po->getSupplier();
        $this->checkDashboardAccess($supplier);

        $email = new RequestToScrapEmail($this->getCurrentUser(), $po);

        $form = $this->createFormBuilder($email)
            ->add('reason', TextareaType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $email->loadSubscribers($this->manager());
            $mailer = $this->get(MailerInterface::class);
            $mailer->send($email);
            $po->setUpdated();
            $this->dbm->flush();
            return $this->dialogRedirect($supplier);
        }

        return [
            'form' => $form->createView(),
            'po' => $po,
            'cancelUri' => $this->getDashboardUri($supplier),
        ];
    }

    /**
     * Allow us to easily set the requested date.
     * @Route("/purchaseorder/{id}/requesteddate/",
     *   name="supplier_purchaseorder_requesteddate")
     * @todo Use a more specific role
     * @Template("supplier/purchaseOrder/requestedDate.html.twig")
     */
    public function requestedDateAction(PurchaseOrder $po, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $options = ['validation_groups' => ['requestedDate']];
        $form = $this->createFormBuilder($po, $options)
            ->add('requestedDate', DateType::class, [
                'required' => false,
            ])
            ->add('submit', SubmitType::class)
            ->getForm();
        $returnTo = $this->getOrderListUri($po->getSupplier());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->flush();
            return $this->redirect($returnTo);
        }

        $levels = $this->get(StockLevelService::class);
        return [
            'po' => $po,
            'form' => $form->createView(),
            'cancelUri' => $returnTo,
            'levels' => $levels,
        ];
    }

    /**
     * List purchase orders that are being shipped directly to the CM.
     *
     * @Route("/{id}/incoming/", name="supplier_incoming")
     * @Template("supplier/purchaseOrder/incoming.html.twig")
     */
    public function incomingAction(Supplier $supplier)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_ADVANCED, Role::EMPLOYEE]);
        $this->checkDashboardAccess($supplier);

        $this->setReturnUri($this->getCurrentUri());

        return [
            'supplier' => $supplier,
            'activeTab' => 'incoming',
        ];
    }

    /**
     * This is a iframe source for @see incomingAction()
     * the template @see incomingAction() extends uses dojo
     * we use iframe so that react does not conflict with dojo
     *
     * @Route("/{id}/incoming/table/", name="supplier_incoming_table")
     * @Method("GET")
     * @Template("supplier/purchaseOrder/incomingTable.html.twig")
     */
    public function incomingTableAction(Supplier $supplier)
    {
        $this->denyAccessUnlessGranted([Role::SUPPLIER_ADVANCED, Role::EMPLOYEE]);
        $this->checkDashboardAccess($supplier);
        $location = $supplier->getFacility();
        $this->setReturnUri($this->getCurrentUri());

        /** @var $repo PurchaseOrderRepository */
        $repo = $this->getRepository(PurchaseOrder::class);
        $orders = $repo->findOpenByDeliveryLocation($location);
        $usefulOrders = array_filter($orders, function(PurchaseOrder $order) {
            foreach ($order->getItems() as $item) {
                if ($item->isStockItem() && $item->getQtyRemaining() > 0) {
                    return true;
                }
            }
            return false;
        });

        /** @var SupplierInvoiceTrackingFacadesFactory $siTrackingFactory */
        $siTrackingFactory = $this->get(SupplierInvoiceTrackingFacadesFactory::class);

        /** @var TrackingFacade[] $trackingRecordFacades */
        $trackingRecordFacades = $siTrackingFactory->fromPurchaseOrders($usefulOrders);


        usort($trackingRecordFacades, function (TrackingFacade $a, TrackingFacade $b) {
            return $a->getPurchaseOrder()->getID() > $b->getPurchaseOrder()->getID() ?
                -1 : 1;
        });

        $serializer = $this->get('serializer');
        $json = $serializer->serialize($trackingRecordFacades, 'json');
        return [
            'supplier' => $supplier,
            'json' => $json
        ];
    }


    /**
     * Pester the supplier by email to take action when the PO has been
     * untouched for too long.
     *
     * @Route("/{order}/pester/", name="supplier_po_pester")
     * @Template("supplier/purchaseOrder/pester.html.twig")
     */
    public function pesterAction(PurchaseOrder $order, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        $email = new Email();
        $email->setContentTypeText();
        $email->setFrom($this->getCurrentUser());
        $email->setSubject("Please update $order");
        $email->setBody($this->renderPesterBody($order));

        $form = $this->createFormBuilder($email)
            ->add('to', MailableType::class, [
                'choices' => $order->getSupplierContacts(),
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('cc', TextMailableType::class, [
                'multiple' => true,
                'required' => false,
            ])
            ->add('subject', TextType::class)
            ->add('body', TextareaType::class)
            ->add('send', SubmitType::class)
            ->getForm();

        $next = $this->getReturnUri($this->getDashboardUri($order->getSupplier()));

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $mailer = $this->get(MailerInterface::class);
            $mailer->send($email);
            $this->logNotice("Email sent.");
            return $this->redirect($next);
        }

        return [
            'form' => $form->createView(),
            'order' => $order,
            'cancelUri' => $next,
        ];
    }

    private function renderPesterBody(PurchaseOrder $order)
    {
        /** @var $engine EngineInterface */
        $engine = $this->get(CmsEngine::class);
        return $engine->render('purchasing.pester', [
            'order' => $order,
            'sender' => $this->getCurrentUser(),
        ]);
    }
}
