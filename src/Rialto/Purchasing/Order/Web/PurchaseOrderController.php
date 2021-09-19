<?php

namespace Rialto\Purchasing\Order\Web;

use Doctrine\ORM\EntityManagerInterface;
use Gumstix\Storage\FileStorage;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Ledger\Entry\GLEntry;
use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Accounting\Supplier\SupplierTransactionRepository;
use Rialto\Database\Orm\EntityList;
use Rialto\Email\EmailException;
use Rialto\Exception\InvalidDataException;
use Rialto\Manufacturing\Allocation\AllocationConfiguration;
use Rialto\Manufacturing\Allocation\AllocationConfigurationArray;
use Rialto\Manufacturing\Allocation\AllocatorIndex;
use Rialto\Manufacturing\Allocation\Command\AllocateCommand;
use Rialto\Manufacturing\Allocation\Orm\AllocationConfigurationRepository;
use Rialto\Manufacturing\Allocation\RequirementAllocator;
use Rialto\Manufacturing\Allocation\WorkOrderAllocator;
use Rialto\Manufacturing\Allocation\WorkOrderAllocatorGroup;
use Rialto\Manufacturing\Bom\BomException;
use Rialto\Manufacturing\PurchaseOrder\Command\OrderPartsCommand;
use Rialto\Manufacturing\PurchaseOrder\Command\UserSelectManufacturerToOrderCommand;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Manufacturing\WorkOrder\WorkOrderCollection;
use Rialto\PcbNg\Exception\PcbNgSubmitterException;
use Rialto\PcbNg\Service\PcbNgClient;
use Rialto\PcbNg\Service\PcbNgSubmitter;
use Rialto\Port\CommandBus\CommandBus;
use Rialto\Purchasing\Invoice\Orm\SupplierInvoiceRepository;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Purchasing\Order\Command\MergePurchaseOrdersCommand;
use Rialto\Purchasing\Order\Email\PurchaseOrderEmail;
use Rialto\Purchasing\Order\OrderPdfGenerator;
use Rialto\Purchasing\Order\Orm\PurchaseOrderRepository;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Order\PurchaseOrderFactory;
use Rialto\Purchasing\Order\PurchaseOrderSender;
use Rialto\Purchasing\Order\SingleItemPurchaseOrder;
use Rialto\Purchasing\Order\StockItemVoter;
use Rialto\Purchasing\Producer\DependencyUpdater;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Security\Privilege;
use Rialto\Security\Role\Role;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Item\StockItem;
use Rialto\Web\Form\Validator;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\Response\PdfResponse;
use Rialto\Web\RialtoController;
use Sabre\VObject\Parser\Json;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use SplObjectStorage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;


/**
 * Controller for creating and editing purchase orders.
 */
class PurchaseOrderController extends RialtoController
{
    /** @var PurchaseOrderSender */
    private $poSender;

    /** @var ValidatorInterface */
    private $validator;

    /** @var CommandBus */
    private $commandBus;

    /** @var AllocationConfigurationRepository */
    private $allocatorConfigurationRepo;

    /** @var AllocationConfiguration[]*/
    private $allocationConfigurations;

    /** @var FileStorage */
    private $storage;

    public function __construct(PurchaseOrderSender $poSender,
                                ValidatorInterface $validator,
                                CommandBus $commandBus,
                                EntityManagerInterface $entityManager,
                                FileStorage $storage)
    {
        $this->poSender = $poSender;
        $this->validator = $validator;
        $this->commandBus = $commandBus;
        $this->allocatorConfigurationRepo = $entityManager->getRepository(AllocationConfiguration::class);
        $this->allocationConfigurations = $this->allocatorConfigurationRepo->findAll();
        $this->storage = $storage;
    }

    /**
     * @Route("/purchasing/order/", name="purchase_order_list")
     * @Method("GET")
     * @Template("purchasing/order/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $form = $this->createForm(ListFilterType::class);
        $form->submit($request->query->all());
        $repo = $this->getRepository(PurchaseOrder::class);
        $list = new EntityList($repo, $form->getData());
        return [
            'form' => $form->createView(),
            'list' => $list,
        ];
    }

    /**
     * @Route("/purchasing/order/{order}/", name="purchase_order_view", options={"expose": true})
     * @Method("GET")
     * @Template("purchasing/order/view.html.twig")
     */
    public function viewAction(PurchaseOrder $order, PcbNgClient $pcbNgClient)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        return [
            'entity' => $order,
            'isSupplierPcbNg' => $order->hasSupplier()
                && ($order->getSupplier() === $pcbNgClient->getPcbNgSupplier()),
        ];
    }

    /**
     * @Route("/po/retrieve/{fileName}/pdf/", name="retrieve_po_pdf")
     * @Method("GET")
     */
    public function retrieveAction(string $fileName)
    {
        $this->denyAccessUnlessGranted([Role::PURCHASING, Role::STOCK]);
        $pdfData = $this->storage->get($fileName);
        return PdfResponse::create($pdfData, $fileName);
    }

    /**
     * @Route("/allocationSourcesPrioritization/", name="allocation_sources_prioritization", options={"expose": true}))
     * @Template("purchasing/order/allocation-sources-prioritization.html.twig")
     */
    public function prioritizeAutoAllocatorSourcesAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);

        $this->allocationConfigurations = $this->allocatorConfigurationRepo->findAll();
        $allocationConfigurationArray = new AllocationConfigurationArray($this->allocationConfigurations);
        $form = $this->createForm(PrioritizeAutoAllocatorSourcesType::class, $allocationConfigurationArray);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($allocationConfigurationArray->getWarehousePriority() == $allocationConfigurationArray->getPurchaseOrderPriority()
            || $allocationConfigurationArray->getWarehousePriority() == $allocationConfigurationArray->getCmPriority()
            || $allocationConfigurationArray->getPurchaseOrderPriority() == $allocationConfigurationArray->getCmPriority()) {
                $msg = "Priority should not be the same.";
                $this->logError($msg);
            } else {
                try {
                    foreach ($this->allocationConfigurations as $allocConfig) {
                        /** @var AllocationConfiguration $allocConfig */
                        if ($allocConfig->getType() === AllocationConfiguration::TYPE_WAREHOUSE_STOCK) {
                            $allocConfig->setPriority($allocationConfigurationArray->getWarehousePriority());
                            $allocConfig->setDisabled($allocationConfigurationArray->getWarehouseDisabled());
                        } elseif ($allocConfig->getType() === AllocationConfiguration::TYPE_PO_ITEMS) {
                            $allocConfig->setPriority($allocationConfigurationArray->getPurchaseOrderPriority());
                            $allocConfig->setDisabled($allocationConfigurationArray->getPurchaseOrderDisabled());
                        } elseif ($allocConfig->getType() === AllocationConfiguration::TYPE_CONTRACT_MANUFACTURER_STOCK) {
                            $allocConfig->setPriority($allocationConfigurationArray->getCmPriority());
                            $allocConfig->setDisabled($allocationConfigurationArray->getCmDisabled());
                        }
                    }
                    $this->dbm->flush();
                    $msg = "Edit Allocation Configurations successfully.";
                    $this->logNotice($msg);
                } catch (InvalidDataException $ex) {
                    $this->logException($ex);
                }
            }
        }
        return [
            'form' => $form->createView(),
            'array' => $allocationConfigurationArray,
        ];
    }

    /**
     * @Route("/purchasing/order/requirementsAndPurchasingData/{id}/", name="requirement_and_purchasing_data_view")
     * @Method({"GET", "POST"})
     * @Template("purchasing/order/requirements-and-purchasing-data-view.html.twig")
     */
    public function viewRequirementsAction(PurchaseOrder $purchaseOrder, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);

        if (!$purchaseOrder->hasWorkOrders()) {
            throw $this->notFound("$purchaseOrder has no work orders");
        }
        if ($purchaseOrder->isCompleted()) {
            throw $this->badRequest("$purchaseOrder is completed");
        }

        $list = WorkOrderCollection::fromPurchaseOrder($purchaseOrder);
        $cm = $purchaseOrder->getBuildLocation();

        $allocator = new WorkOrderAllocator($list);

        if ($request->get('fromCM') && (!$cm->isHeadquarters())) {
            $allocator->addLocation($cm);
            $allocator->setShareBins(true);
        }
        $allocator->addLocation($this->getHeadquarters());
        $allocator->setBuildLocation($purchaseOrder->getBuildLocation());
        $allocator->setAllocationConfigurations($this->allocationConfigurations);
        $allocator->createItems($this->dbm);
        $allocator->loadPurchasingData($this->dbm);
        $allocator->validate($this->validator);

        $buildLocation = $purchaseOrder->getBuildLocation();

        $index = new AllocatorIndex($allocator);
        $canOrder = $this->isGranted(Role::PURCHASING);
        $index->setCanOrder($canOrder);

        $reqAllocToOrder = $this->serializeGroup($index->getGroupToOrder(), $index->getGroupToAllocate());

        $totalToAllocate = $index->getGroupToOrder()->getTotalStillNeeded();

        return [
            'po' => $purchaseOrder,
            'location' => $cm,
            'index' => $index,
            'buildLocation' => $buildLocation,
            'reqAllocToOrder' => $reqAllocToOrder,
            'totalToAllocate' => $totalToAllocate,
        ];
    }

    /**
     * @param WorkOrderAllocatorGroup $toOrder
     * @param WorkOrderAllocatorGroup $toAllocate
     * @return string[]
     */
    private function serializeGroup(WorkOrderAllocatorGroup $toOrder, WorkOrderAllocatorGroup $toAllocate)
    {
        $items = array_merge($toOrder->getItems(), $toAllocate->getItems());
        return array_map(function (RequirementAllocator $reqAllocator) {
            $candidates = [];

            foreach ($reqAllocator->getCandidateSources() as $sourceCollection) {
                foreach ($sourceCollection->getSources() as $basicStockSource) {
                    $stockAllocations = $basicStockSource->getAllocations();
                    $stockAllocationsInfoArray = [];
                    foreach ($stockAllocations as $stockAllocation) {
                        $consumer =$stockAllocation->getConsumer();
                        if ($consumer instanceof WorkOrder) {
                            $stockAllocationsInfo = ['allocation' => $consumer->getPurchaseOrder()->getId(),
                                                     'qtyAllocated' => $stockAllocation->getQtyAllocated(),
                                                     'type' => "Work Order"];
                            array_push($stockAllocationsInfoArray, $stockAllocationsInfo);
                        } elseif ($consumer instanceof SalesOrderDetail) {


                            $stockAllocationsInfo = ['allocation' => $consumer->getSalesOrder()->getId(),
                                                     'qtyAllocated' => $stockAllocation->getQtyAllocated(),
                                                     'type' => "Sales Order"];
                            array_push($stockAllocationsInfoArray, $stockAllocationsInfo);
                        }
                    }
                    if ($basicStockSource instanceof StockBin) {
                        $candidate = ['basicStockSource' => $basicStockSource->getId(),
                                      'canBeAllocated' => $basicStockSource->getCanBeAllocated(),
                                      'qtyRemaining' => $basicStockSource->getQtyRemaining(),
                                      'allocInfo' => $stockAllocationsInfoArray,
                                      'type' => "bin",
                                      'style' => $basicStockSource->getBinStyle()->getCategory(),
                                      'location' => $basicStockSource->getLocation()->getName(),
                                      'poId' => null];
                        array_push($candidates, $candidate);
                    } elseif ($basicStockSource instanceof StockProducer) {
                        $po = $basicStockSource->getPurchaseOrder();
                        $candidate = ['basicStockSource' => $basicStockSource->getId(),
                                      'canBeAllocated' => $basicStockSource->getCanBeAllocated(),
                                      'qtyRemaining' => $basicStockSource->getQtyRemaining(),
                                      'allocInfo' => $stockAllocationsInfoArray,
                                      'type' => "po",
                                      'style' => "PO",
                                      'location' => $po->getId(),
                                      'poId' => $po->getId()];
                        array_push($candidates, $candidate);
                    }
                }
            }

            $purchData = $reqAllocator->getStockItem()->getPreferredPurchasingData();
            return [
                'fullSku' => $reqAllocator->getFullSku(),
                'description' => $reqAllocator->getDescription(),
                'qtyNeeded' => $reqAllocator->getQtyNeeded(),
                'qtyAllocated' => $reqAllocator->getQtyAllocated(),
                'qtyStillNeeded' => $reqAllocator->getQtyStillNeeded(),
                'highestCandidatePriority' => $reqAllocator->getHighestCandidatePriority(),
                'purchData' => $purchData ? [
                    'id' => $purchData->getId(),
                    'supplier' => $purchData->getSupplier()->getName(),
                    'moq' => (int)$purchData->getMinimumOrderQty(),
                    'minimumCost' => $purchData->getCost($purchData->getMinimumOrderQty()),
                    'eoq' => (int)$purchData->getEconomicOrderQty(),
                    'economicCost' => $purchData->getCostAtEoq(),
                    'incrementQty' => $purchData->getIncrementQty(),
                    'unitsPerBin' => $purchData->getBinSize(),
                    'currentStockLevel' => $purchData->getStockLevel(),
                    'lastSync' => $purchData->getLastSync() ? $purchData->getLastSync()->format('Y-m-d') : '',
                ] : null,
                'candidates' => $candidates,
            ];
        }, $items);
    }

    /**
     * @Route("/order-parts/command/{po}/", name="order_command_for_po", options={"expose": true})
     * @Method("POST")
     */
    public function orderAction(PurchaseOrder $po)
    {
        $this->denyAccessUnlessGranted([Role::EMPLOYEE]);
        $command = new OrderPartsCommand($po->getId());
        $this->commandBus->handle($command);

        $this->dbm->flush();
        $msg = "Order Parts for $po successfully.";
        $this->logNotice($msg);
        return $this->redirectToRoute('requirement_and_purchasing_data_view', [
            'id' => $po->getId(),
        ]);
    }

    /**
     * @Route("/allocate/command/{po}/", name="allocate_command_for_po", options={"expose": true})
     * @Method("POST")
     */
    public function allocateAction(PurchaseOrder $po, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::EMPLOYEE]);
        $selected = $request->get('selected', []);
        $command = new AllocateCommand($po->getId());
        $command->setUserSelectionSourcesIds($selected);
        $this->commandBus->handle($command);

        $this->dbm->flush();
        $msg = "Allocate for $po successfully.";
        $this->logNotice($msg);
        return $this->redirectToRoute('requirement_and_purchasing_data_view', [
            'id' => $po->getId(),
        ]);
    }

    /**
     * @Route("/order-parts-and-allocate/command/{po}/", name="order_and_allocate_command_for_po", options={"expose": true})
     * @Method("POST")
     */
    public function orderPartsAndAllocateAction(PurchaseOrder $po, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::EMPLOYEE]);
        $selected = $request->get('selected', []);
        $command = new UserSelectManufacturerToOrderCommand($po->getId());
        $command->setUserSelectionSourcesIds($selected);
        $this->commandBus->handle($command);

        $this->dbm->flush();
        $msg = "Order Parts and Allocate for $po successfully.";
        $this->logNotice($msg);
        return $this->redirectToRoute('requirement_and_purchasing_data_view', [
            'id' => $po->getId(),
        ]);
    }

    /**
     * Allow the user to search for a PO and jump directly to it if it is
     * found.
     *
     * @Route("/purchasing/select-order/", name="purchase_order_select")
     * @Method("GET")
     */
    public function selectAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        /** @var $order PurchaseOrder */
        $order = $this->needEntityFromRequest(PurchaseOrder::class, 'orderNo', $request);
        $url = $this->isGranted(Privilege::EDIT, $order)
            ? $this->generateUrl("purchase_order_edit", [
                'order' => $order->getId(),
            ])
            : $this->viewUrl($order);
        return $this->redirect($url);
    }


    /**
     * @Route("/Purchasing/PurchaseOrder", name="Purchasing_PurchaseOrder_create")
     * @Template("purchasing/order/create.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        $form = $this->createForm(CreatePurchaseOrderType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $order = $form->getData();
                $this->dbm->persist($order);
                $this->dbm->flushAndCommit();
                $this->logNotice(ucfirst("$order created successfully."));
                return $this->redirectToRoute('purchase_order_edit', [
                    'order' => $order->getId(),
                ]);
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * @Route("/Purchasing/PurchaseOrder/{order}",
     *     name="purchase_order_edit",
     *     requirements={"order" = "^\d+$"})
     * @Template("purchasing/order/edit.html.twig")
     */
    public function editAction(PurchaseOrder $order,
                               Request $request,
                               DependencyUpdater $dependencyUpdater)
    {
        $this->denyAccessUnlessGranted(Privilege::EDIT, $order);
        $this->denyAccessUnlessGranted([Role::PURCHASING, Role::WAREHOUSE]);

        $form = $this->createForm(EditPurchaseOrderType::class, $order);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->get('addNewItem')) {
                if ($order->hasNewItem()) {
                    $order->addItemFromPurchasingData($order->getNewItem());
                } elseif ($this->isGranted(Role::PURCHASING)) {
                    $account = GLAccount::fetchDevelopmentExpense($this->dbm);
                    $order->addNonStockItem($account);
                }
            } elseif ($request->get('removeItem')) {
                $itemId = $request->get('removeItem');
                $order->removeItemById($itemId);
            }

            $this->dbm->beginTransaction();
            try {
                $dependencyUpdater->updatePurchaseOrder($order);
                $this->dbm->flushAndCommit();
            } catch (BomException $ex) {
                $this->dbm->rollBack();
                $this->logException($ex);
                return $this->redirectToOrder($order);
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $this->logNotice(ucfirst("$order updated successfully."));
            return $this->redirectToRoute('purchase_order_edit', [
                'order' => $order->getId(),
            ]);
        }

        return [
            'order' => $order,
            'form' => $form->createView(),
            'cancelUri' => $this->viewUrl($order),
        ];
    }

    private function redirectToOrder(PurchaseOrder $order)
    {
        $url = $this->viewUrl($order);
        return $this->redirect($url);
    }

    private function viewUrl(PurchaseOrder $order)
    {
        return $this->generateUrl('purchase_order_view', [
            'order' => $order->getId(),
        ]);
    }

    /**
     * @Route("/purchasing/order/{order}/send/", name="purchase_order_send")
     * @Template("purchasing/order/send.html.twig")
     */
    public function sendAction(PurchaseOrder $order, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);

        if (!$this->canBeSent($order)) {
            return $this->redirectToOrder($order);
        }

        $email = $this->poSender->createEmail($order);
        $form = $this->createForm(PurchaseOrderEmailType::class, $email);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            return $this->sendEmail($email, $order);
        }

        return [
            'order' => $order,
            'email' => $email,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/purchasing/order/{order}/sendnow/", name="purchase_order_sendnow")
     * @Template("purchasing/order/send-now.html.twig")
     */
    public function sendNowAction(PurchaseOrder $order, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        $url = $this->viewUrl($order);
        if (!$this->canBeSent($order)) {
            return new Response(ucfirst("$order cannot be sent."));
        }

        $email = $this->poSender->createEmail($order);
        $form = $this->createForm(PurchaseOrderQuickEmailType::class, $email);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->sendEmail($email, $order);
            return JsonResponse::javascriptRedirect($url);
        }

        return [
            'order' => $order,
            'form' => $form->createView(),
        ];
    }

    private function canBeSent(PurchaseOrder $order)
    {
        if ($order->isCompleted()) {
            throw $this->badRequest("$order is completed");
        }
        if (!$order->hasSupplier()) {
            throw $this->badRequest("$order has no supplier");
        }
        $vGroups = ['Default', 'purchasing'];
        $errors = $this->validator->validate($order, null, $vGroups);
        if (count($errors) > 0) {
            $this->logErrors($errors);
            return false;
        }
        if (!$order->canBeSent()) {
            $this->logError(ucfirst("$order is not ready to be sent yet."));
            return false;
        }

        return true;
    }

    private function sendEmail(PurchaseOrderEmail $email, PurchaseOrder $order)
    {
        $this->dbm->beginTransaction();
        try {
            $this->poSender->sendEmail($email, $order);
            $this->dbm->flushAndCommit();
        } catch (EmailException $ex) {
            $this->dbm->rollBack();
            $this->logException($ex);
            return $this->redirect($this->getCurrentUri());
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }

        $this->logNotice('Email sent successfully.');
        return $this->redirectToOrder($order);
    }

    /**
     * @Route("/purchasing/order/{order}/sent/", name="purchasing_order_set_sent")
     * @Method("PUT")
     */
    public function markAsSentAction(PurchaseOrder $order)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        if (!$this->canBeSent($order)) {
            return $this->redirectToOrder($order);
        }

        $this->dbm->beginTransaction();
        try {
            $this->poSender->markAsSent($order);
            $this->dbm->flushAndCommit();
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }

        $this->logNotice("$order has been marked as sent.");
        return $this->redirectToOrder($order);
    }

    /**
     * @Route("/Purchasing/SingleItemPurchaseOrder/{stockCode}",
     *   name="Purchasing_PurchaseOrder_singleItem")
     * @Template("purchasing/order/sipo.html.twig")
     */
    public function createSingleItemOrderAction(StockItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::PURCHASING, Role::WAREHOUSE]);
        if ($item->isDiscontinued()) {
            throw $this->badRequest("$item is discontinued");
        }
        if (!$item->isPurchased()) {
            throw $this->badRequest("$item is not a purchased item");
        }
        $this->checkSipoPrivileges($item);

        $sipo = new SingleItemPurchaseOrder($item);
        $form = $this->createForm(SingleItemOrderType::class, $sipo);

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            $sipo->loadPurchasingData($this->dbm);
            /** @var $validator Validator */
            $validator = $this->get(Validator::class);
            $validator->validate($form, $sipo, 'purchData');

            if ($form->isValid()) {
                $this->dbm->beginTransaction();
                try {
                    /** @var $factory PurchaseOrderFactory */
                    $factory = $this->get(PurchaseOrderFactory::class);
                    $purchOrder = $factory->forSingleItem($sipo);
                    $this->dbm->persist($purchOrder);
                    $this->dbm->flushAndCommit();
                } catch (\Exception $ex) {
                    $this->dbm->rollBack();
                    throw $ex;
                }
                $this->logNotice("Created $purchOrder.");
                return $this->redirectToRoute('purchase_order_edit', [
                    'order' => $purchOrder->getId(),
                ]);
            }
        }

        return [
            'item' => $item,
            'form' => $form->createView(),
            'cancelUri' => $this->generateUrl('stock_item_view', [
                'item' => $item->getSku(),
            ]),
        ];
    }

    /**
     * True if the current user is allowed to purchase $item.
     */
    private function checkSipoPrivileges(StockItem $item)
    {
        if (!$this->isGranted(StockItemVoter::PURCHASE, $item)) {
            throw $this->forbidden();
        }
    }

    /**
     * @Route("/purchasing/order/{id}/csv/", name="purchase_order_csv")
     * @Method("GET")
     */
    public function csvAction(PurchaseOrder $order)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        $csv = PurchaseOrderCsv::create($order);
        $csv->useWindowsNewline();
        $filename = str_replace(" ", "_", "$order.csv");
        return FileResponse::fromData($csv->toString(), $filename, 'text/csv');
    }

    /**
     * @Route("/purchasing/order/{id}/pdf/", name="purchase_order_pdf")
     * @Method("GET")
     */
    public function pdfAction(PurchaseOrder $order, OrderPdfGenerator $pdfGenerator)
    {
        $this->denyAccessUnlessGranted([Role::PURCHASING, Role::STOCK]);
        if (!$order->hasSupplier()) {
            throw $this->badRequest("$order is an in-house order");
        }
        $pdfData = $pdfGenerator->generatePdf($order);
        $filename = str_replace(" ", "_", "$order.pdf");
        return PdfResponse::create($pdfData, $filename);
    }

    /**
     * Shows all GL entries associated with the given PO.
     *
     * @Route("/Purchasing/PurchaseOrder/{id}/entries",
     *  name="Purchasing_PurchaseOrder_entries")
     * @Method("GET")
     * @Template("purchasing/order/entries.html.twig")
     */
    public function entriesAction(PurchaseOrder $order)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $totals = new SplObjectStorage();

        $receiptEntries = [];
        foreach ($order->getReceipts() as $grn) {
            foreach ($grn->getGLEntries() as $entry) {
                $receiptEntries[] = $entry;
                $this->addEntryToTotals($entry, $totals);
            }
        }

        /** @var $invoiceRepo SupplierInvoiceRepository */
        $invoiceRepo = $this->getRepository(SupplierInvoice::class);
        /** @var $transRepo SupplierTransactionRepository */
        $transRepo = $this->getRepository(SupplierTransaction::class);
        $invoiceEntries = [];
        foreach ($invoiceRepo->findByPurchaseOrder($order) as $inv) {
            foreach ($transRepo->findByInvoice($inv) as $suppTrans) {
                foreach ($suppTrans->getGLEntries() as $entry) {
                    $invoiceEntries[] = $entry;
                    $this->addEntryToTotals($entry, $totals);
                }
            }
        }

        return [
            'po' => $order,
            'receiptEntries' => $receiptEntries,
            'invoiceEntries' => $invoiceEntries,
            'totals' => $totals,
        ];
    }

    private function addEntryToTotals(GLEntry $entry, SplObjectStorage $totals)
    {
        if (!isset($totals[$entry->getAccount()])) {
            $totals[$entry->getAccount()] = 0;
        }
        $totals[$entry->getAccount()] += $entry->getAmount();
    }

    /**
     * @Route("/record/Purchasing/PurchaseOrder/{id}/",
     *   name="Purchasing_PurchaseOrder_delete")
     * @Method("DELETE")
     */
    public function deleteAction(PurchaseOrder $po)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        if (!$this->isGranted(Privilege::DELETE, $po)) {
            throw $this->badRequest("$po cannot be deleted.");
        }

        $msg = "Deleted $po successfully.";

        $this->dbm->remove($po);
        $this->dbm->flush();

        $this->logNotice($msg);
        return $this->redirect($this->generateUrl('index'));
    }

    /**
     * @Route("/purchasing/order/{id}/mergeCandidates",
     *   name="purchase_order_merge_candidates")
     * @Template("purchasing/order/merge-candidates.html.twig")
     */
    public function listMergeCandidates(PurchaseOrder $po)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        if (!$this->isGranted(Privilege::DELETE, $po)) {
            throw $this->badRequest("$po cannot be merged.");
        }

        /** @var PurchaseOrderRepository $repo */
        $repo = $this->getRepository(PurchaseOrder::class);
        $candidates = $repo->createBuilder()
            ->bySupplier($po->getSupplier())
            ->byDeliveryLocation($po->getDeliveryLocation())
            ->isOpen()
            ->isNotSent()
            ->getQueryBuilder()
            ->andWhere('po != :order')
            ->setParameter('order', $po)
            ->getQuery()
            ->getResult();

        return [
            'order' => $po,
            'candidates' => $candidates,
        ];
    }

    /**
     * @Route("/purchasing/order/{id}/merge",
     *   name="purchase_order_merge")
     * @Method("POST")
     */
    public function mergeAction(PurchaseOrder $po, Request $request)
    {
        $primary = $request->get('primary');
        if ($primary === null) {
            throw $this->badRequest("Missing ID of PO to merge into.");
        }

        $this->denyAccessUnlessGranted(Role::PURCHASING);
        if (!$this->isGranted(Privilege::DELETE, $po)) {
            throw $this->badRequest("$po cannot be merged.");
        }

        $msg = "PO {$po->getId()} merged into PO $primary";

        $command = new MergePurchaseOrdersCommand($primary, $po->getId());
        $this->commandBus->handle($command);

        $this->logNotice($msg);

        return $this->redirect($this->generateUrl('purchase_order_view', [
            'order' => $primary,
        ]));
    }

    /**
     * @Route("/Purchasing/PurchaseOrder/{id}/pcbNg/submitBuildFiles",
     *  name="Purchasing_PurchaseOrder_PcbNg_SubmitBuildFiles")
     * @Method("POST")
     */
    public function submitBuildFilesToPcbNgAction(PurchaseOrder $order,
                                                  PcbNgSubmitter $pcbNgSubmitter,
                                                  PcbNgClient $pcbNgClient)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);

        try {
            $pcbNgUserBoard = $pcbNgSubmitter->submitPo(
                "Purchase order {$order->getId()}",
                $order);
            $pcbNgBoardUrl = $pcbNgClient->getStorefrontBoardUrl($pcbNgUserBoard->getId());
            return new RedirectResponse($pcbNgBoardUrl);

        } catch (PcbNgSubmitterException $exception) {
            throw $this->badRequest($exception->getMessage());
        }
    }
}
