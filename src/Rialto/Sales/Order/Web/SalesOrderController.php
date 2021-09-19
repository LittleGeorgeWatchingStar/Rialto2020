<?php

namespace Rialto\Sales\Order\Web;

use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\View\View;
use Rialto\Allocation\AllocationEvents;
use Rialto\Allocation\Consumer\StockConsumerEvent;
use Rialto\Cms\CmsEngine;
use Rialto\Database\Orm\EntityList;
use Rialto\Email\MailerInterface;
use Rialto\Geography\Address\Address;
use Rialto\Payment\CardAuth;
use Rialto\Payment\GatewayException;
use Rialto\Payment\Web\CardAuthType;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Customer\CustomerBranch;
use Rialto\Sales\Discount\DiscountCalculator;
use Rialto\Sales\Order\CustomerPartNoPopulator;
use Rialto\Sales\Order\Email\SalesOrderEmail;
use Rialto\Sales\Order\Payment\CapturedSalesInvoiceItems;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Sales\Order\SalesOrderEvent;
use Rialto\Sales\Order\SalesOrderInterface;
use Rialto\Sales\Order\SalesOrderPaymentProcessor;
use Rialto\Sales\SalesEvents;
use Rialto\Sales\SalesLogger;
use Rialto\Sales\SalesPdfGenerator;
use Rialto\Sales\Shipping\SalesOrderShippingApproval;
use Rialto\Sales\Web\SalesRouter;
use Rialto\Security\Role\Role;
use Rialto\Shipping\Export\DeniedPartyException;
use Rialto\Shipping\Export\DeniedPartyScreener;
use Rialto\Shipping\Method\Web\ShippingMethodType;
use Rialto\Shipping\Shipment\ShipmentFactory;
use Rialto\Shipping\Shipment\Web\ShipmentOptionFacade;
use Rialto\Shipping\Shipper\Shipper;
use Rialto\Tax\TaxLookup;
use Rialto\Ups\Shipping\Webservice\UpsXmlError;
use Rialto\Ups\TrackingRecord\TrackingRecord;
use Rialto\Ups\TrackingRecord\TrackingRecordRepository;
use Rialto\Web\Form\JsEntityType;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\Response\PdfResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for managing sales orders.
 *
 * @see SalesOrder
 */
class SalesOrderController extends RialtoController
{
    /**
     * @var SalesRouter
     */
    private $router;

    /**
     * @var SalesPdfGenerator
     */
    private $pdfGenerator;

    /**
     * @var ShipmentFactory
     */
    private $shipmentFactory;

    /** @var TrackingRecordRepository */
    private $TrackingRecordRepo;

    protected function init(ContainerInterface $container)
    {
        $this->router = $this->get(SalesRouter::class);
        $this->pdfGenerator = $this->get(SalesPdfGenerator::class);
        $this->shipmentFactory = $this->get(ShipmentFactory::class);
        $em = $this->get(EntityManagerInterface::class);
        $this->TrackingRecordRepo = $em->getRepository(TrackingRecord::class);
    }

    /**
     * @Route("/sales/order/", name="sales_order_list")
     * @Template("sales/order/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::CUSTOMER_SERVICE);
        $form = $this->createForm(ListFilterType::class);
        $form->submit($request->query->all());
        $repo = $this->getRepository(SalesOrder::class);
        $results = new EntityList($repo, $form->getData());
        if ($request->get('csv')) {
            $csv = SalesOrderCsv::create($results);
            return FileResponse::fromData($csv->toString(), 'sales orders.csv', 'text/csv');
        }

        return [
            'form' => $form->createView(),
            'orders' => $results,
        ];
    }

    /**
     * @Route("/sales/order/captured-for-email/{email}/")
     * @Route("/api/v2/sales/order/captured-for-email/{email}/")
     * @Method("GET")
     */
    public function listCapturedSkusForEmail(string $email)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        return View::create($this->listUniqueCapturedSkusForEmail($email));
    }

    /**
     * @param string $email
     * @return string[]
     */
    private function listUniqueCapturedSkusForEmail(string $email): array
    {
        $repo = $this->getRepository(SalesOrder::class);
        $results = new EntityList($repo, ['customer' => $email]);

        $capturedItems = CapturedSalesInvoiceItems::fromSalesOrders(
            $results->getIterator()->getArrayCopy());

        return $capturedItems->getUniqueSkus();
    }

    /**
     * View a single sales order in detail.
     *
     * @Route("/sales/order/{order}/", name="sales_order_view", options={"expose": true})
     * @Template("sales/order/view.html.twig")
     */
    public function viewAction(SalesOrder $order)
    {
        $invoices = $order->getInvoices();
        $trackingNumbers = [];
        foreach ($invoices as $invoice) {
            $trackingNumbers[] = $invoice->getConsignment();
        }
        $trackingRecords = $this->TrackingRecordRepo->getByTrackingNumbers($trackingNumbers);

        $trackingNumberToRecordDictionary = [];

        foreach ($trackingRecords as $trackingRecord) {
            $trackingNumberToRecordDictionary[$trackingRecord->getTrackingNumber()] = $trackingRecord;
        }

        $this->denyAccessUnlessGranted(Role::CUSTOMER_SERVICE);
        return [
            'entity' => $order,
            'trackingNumberToRecord' => $trackingNumberToRecordDictionary
        ];
    }

    /**
     * @Route("/sales/create-order/", name="sales_order_create")
     * @Template("sales/order/create.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        $customerId = $request->get('customerId');
        $branchId = $request->get('branchId');
        $order = null;
        $cancelUri = null;

        if ($branchId) {  // Create the order
            /** @var $branch CustomerBranch */
            $branch = $this->needEntity(CustomerBranch::class, $branchId);
            $order = new SalesOrder($branch);
            $order->setCreatedBy($this->getCurrentUser());
            $form = $this->createForm(SalesOrderType::class, $order);
            $cancelUri = $this->generateUrl('sales_order_create', [
                'customerId' => $customerId,
            ]);
        } elseif ($customerId) {  // Choose the branch
            /** @var $customer Customer */
            $customer = $this->needEntity(Customer::class, $customerId);
            $form = $this->createFormBuilder()
                ->add('customerBranch', EntityType::class, [
                    'class' => CustomerBranch::class,
                    'choices' => $customer->getBranches(),
                    'choice_label' => 'branchName',
                    'label' => 'Customer branch',
                ])
                ->add('submit', SubmitType::class)
                ->getForm();
            $cancelUri = $this->generateUrl('sales_order_create');
        } else {  // Choose the customer
            $form = $this->createFormBuilder()
                ->add('customer', JsEntityType::class, [
                    'class' => Customer::class,
                    'choice_label' => 'name',
                    'label' => 'Select a customer',
                ])
                ->add('submit', SubmitType::class)
                ->getForm();
        }

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if (! $order) {
                $data = $form->getData();
                if (isset($data['customer'])) {
                    $customer = $data['customer'];
                    $uri = $this->generateUrl('sales_order_create', [
                        'customerId' => $customer->getId(),
                    ]);
                    return $this->redirect($uri);
                } elseif (isset($data['customerBranch'])) {
                    /** @var $branch CustomerBranch */
                    $branch = $data['customerBranch'];
                    $uri = $this->generateUrl('sales_order_create', [
                        'branchId' => $branch->getId(),
                    ]);
                    return $this->redirect($uri);
                }
            } else {
                if ($form->isValid()) {
                    $order->addNewItem($this->dbm);
                    $this->dbm->persist($order);
                    $this->dbm->flush();
                    $this->logNotice(ucfirst("$order created successfully."));
                    $uri = $this->generateUrl('sales_order_edit', [
                        'order' => $order->getId(),
                    ]);
                    return $this->redirect($uri);
                }
            }
        }
        return [
            'order' => $order,
            'form' => $form->createView(),
            'cancelUri' => $cancelUri,
        ];
    }

    /**
     * @Route("/sales/order/{order}/edit/", name="sales_order_edit")
     * @Route("/Sales/SalesOrder/{id}", name="Sales_SalesOrder_edit")
     * @Method({"GET", "POST"})
     * @Template("sales/order/edit.html.twig")
     */
    public function editAction(SalesOrder $order, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        if ($order->isCompleted()) {
            if ($this->isGranted(Role::ADMIN)) {
                $this->logWarning("You are editing a completed sales order.");
            } else {
                $this->logError("Cannot edit a completed sales order.");
                return $this->redirectToOrder($order);
            }
        }

        $form = $this->createForm(SalesOrderEditType::class, $order);

        if ($request->isMethod('POST')) {
            if ($request->get('deleteItem')) {
                $lineItemId = $request->get('deleteItem');
                /** @var SalesOrderDetail $lineItem */
                $lineItem = $this->dbm->need(SalesOrderDetail::class, $lineItemId);
                if ($lineItem->isInvoiced()) {
                    throw $this->badRequest("Cannot delete an invoiced item");
                }
                // We cannot cascade the deletion of the SalesOrderDetail to the Requirement.
                foreach ($lineItem->getRequirements() as $requirement) {
                    if ($requirement->getId()) {
                        $this->dbm->remove($requirement);
                    }
                }
                $this->dbm->remove($lineItem);
                $this->dbm->flush();
                $uri = $this->getCurrentUri(); /* Stay on the editing page */
                $this->logNotice("Line item {$lineItem->getDescription()} deleted successfully.");
                return $this->redirect($uri);
            } else {
                $form->handleRequest($request);
                if ($form->isValid()) {
                    $uri = $this->orderUrl($order);
                    if ($order->hasNewItem()) {
                        $order->addNewItem($this->dbm);
                    }
                    if ($form->get('addNewItem')->isClicked()) {
                        $uri = $this->getCurrentUri(); /* Stay on the editing page */
                    }

                    $this->dbm->beginTransaction();
                    try {
                        $order->resetRequirements($this->dbm);
                        $this->dbm->flush(); // delete old allocations
                        $event = new StockConsumerEvent($order->getLineItems());
                        $this->dispatchEvent(AllocationEvents::STOCK_CONSUMER_CHANGE, $event);
                        $this->dbm->flushAndCommit();
                        $this->logNotice(ucfirst("$order updated successfully."));
                        return $this->redirect($uri);
                    } catch (\Exception $ex) {
                        $this->dbm->rollBack();
                        throw $ex;
                    }
                }
            }
        }

        return [
            'order' => $order,
            'form' => $form->createView(),
            'cancelUri' => $this->orderUrl($order),
        ];
    }

    private function redirectToOrder(SalesOrderInterface $order)
    {
        $url = $this->orderUrl($order);
        return $this->redirect($url);
    }

    private function orderUrl(SalesOrderInterface $order)
    {
        return $this->router->orderView($order);
    }

    /**
     * @Route("/Sales/SalesOrder/{id}/pdf", name="Sales_SalesOrder_pdf",
     *   options={"expose"=true})
     */
    public function pdfAction(SalesOrder $order, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::SALES, Role::CUSTOMER_SERVICE]);
        $pdfData = $this->generatePdf($order, $request);
        $filename = SalesOrderEmail::getPdfFilename(
            $this->getDefaultCompany(),
            $order,
            $request->get('pdfType'));
        if ($request->isMethod('POST')) {
            $order->updateDatePrinted();
            $this->dbm->flush();
        }
        return PdfResponse::create($pdfData, $filename);
    }

    private function generatePdf(SalesOrder $order, Request $request)
    {
        $pdfType = $request->get('pdfType');
        return $this->pdfGenerator->generatePdf($order, $pdfType);
    }

    /**
     * @Route("/sales/order/{id}/email/", name="sales_order_email")
     * @Route("/Sales/SalesOrder/{id}/email", name="Sales_SalesOrder_email")
     * @Template("sales/order/email.html.twig")
     */
    public function emailAction(SalesOrder $order, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::SALES, Role::CUSTOMER_SERVICE]);
        $email = new SalesOrderEmail($this->getDefaultCompany(), $order);
        $form = $this->createForm(SalesOrderEmailType::class, $email, [
            'order' => $order,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $email->createAttachment($this->pdfGenerator);
                $mailer = $this->get(MailerInterface::class);
                $mailer->send($email);
                $order->updateDatePrinted();
                $this->dbm->flush();

                $this->logNotice('Email sent successfully.');
                $uri = $this->orderUrl($order);
                return JsonResponse::javascriptRedirect($uri);
            } else {
                return JsonResponse::fromInvalidForm($form);
            }
        }
        return [
            'order' => $order,
            'email' => $email,
            'formAction' => $this->getCurrentUri(),
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/Sales/SalesOrder/{order}/email/{entry}/",
     *   name="Sales_SalesOrder_emailBody",
     *   options={"expose"=true})
     */
    public function renderEmailBodyAction(SalesOrder $order, $entry, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::SALES, Role::CUSTOMER_SERVICE]);
        $engine = $this->get(CmsEngine::class);
        $body = $engine->render($entry, [
            'order' => $order,
            'customer' => $order->getCustomer(),
            'attachment' => $request->get('attachment'),
        ]);

        $body = str_replace(PHP_EOL, '', $body);
        $body = str_replace('<br />', PHP_EOL, $body);
        $body = strip_tags($body);

        return new Response($body);
    }

    /**
     * @Route("/sales/order/{id}/approve/", name="sales_order_approve")
     * @Template("sales/order/approve.html.twig")
     */
    public function approveAction(SalesOrder $order, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $messages = [];
        $allowOverride = false;
        $allowForceState = false;
        if ($request->isMethod('POST')) {
            if ($request->get('forceState')) {
                $shippingAddress = $order->getShippingAddress()->toArray();
                if (!in_array('stateCode', $shippingAddress)) {
                    $shippingAddress['stateCode'] = $shippingAddress['countryCode'];
                    $order->setShippingAddress(Address::fromArray($shippingAddress));
                    $this->dbm->flush();
                }
                $billingAddress = $order->getBillingAddress()->toArray();
                if (!in_array('stateCode', $billingAddress)) {
                    $billingAddress['stateCode'] = $billingAddress['countryCode'];
                    $order->setBillingAddress(Address::fromArray($shippingAddress));
                    $this->dbm->flush();
                }
            }

            /* @var $service SalesOrderShippingApproval */
            $service = $this->get(SalesOrderShippingApproval::class);
            $messages = $service->validate($order);

            if ($service->canApproveToShip($messages, $request->get('override') !== null)) {
                $this->dbm->beginTransaction();
                try {
                    $order->approveToShip($this->dispatcher());
                    $this->dbm->flushAndCommit();
                } catch (\Exception $ex) {
                    $this->dbm->rollBack();
                    throw $ex;
                }

                $unapproveUrl = $this->generateUrl('sales_order_unapprove', [
                    'id' => $order->getId(),
                ]);
                return $this->redirect($unapproveUrl);
            } elseif ($service->canOverrideWarnings($messages)) {
                $allowOverride = true;
            } elseif ($service->canForceState($messages)) {
                $allowForceState = true;
            }

        }
        return [
            'order' => $order,
            'messages' => $messages,
            'allowOverride' => $allowOverride,
            'allowForceState' => $allowForceState,
        ];
    }

    /**
     * @Route("/sales/order/{id}/unapprove/", name="sales_order_unapprove")
     * @Template("sales/order/unapprove.html.twig")
     */
    public function unapproveAction(SalesOrder $order, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        if ($request->isMethod('POST')) {
            $order->unapproveToShip();
            $this->dbm->flush();

            $this->logNotice("Date to ship has been removed from $order.");
            $approveUrl = $this->generateUrl('sales_order_approve', [
                'id' => $order->getId(),
            ]);
            return $this->redirect($approveUrl);
        }
        return [
            'order' => $order,
        ];
    }

    /**
     * @Route("/Sales/SalesOrder/{id}/recalcConfirmation",
     *   name="Sales_SalesOrder_recalcConfirmation")
     * @Method("POST")
     */
    public function recalcConfirmationAction(SalesOrder $order)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        try {
            $new_amt = $order->getDefaultDepositAmount();
            $order->setDepositAmount($new_amt);
            $this->dbm->flush();
            $this->logNotice(sprintf(
                'Prepayment amount is now $%s (%s%%).',
                number_format($new_amt, 2),
                number_format(SalesOrder::DEPOSIT_FRACTION * 100)
            ));
        } catch (\Exception $ex) {
            $this->logException($ex);
        }

        $uri = $this->orderUrl($order);
        return JsonResponse::javascriptRedirect($uri);
    }

    /**
     * @Route("/Sales/SalesOrder/{id}/recalcDiscounts",
     *   name="Sales_SalesOrder_recalcDiscounts")
     * @Method("POST")
     */
    public function recalcDiscountsAction(SalesOrder $order)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        /** @var $calculator DiscountCalculator */
        $calculator = $this->get(DiscountCalculator::class);

        $this->dbm->beginTransaction();
        try {
            $calculator->updateDiscounts($order);
            $this->dbm->flushAndCommit();
            $this->logNotice('Line item discounts have been recalculated.');
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            $this->logException($ex);
        }

        $uri = $this->orderUrl($order);
        return JsonResponse::javascriptRedirect($uri);
    }

    /**
     * @Route("/Sales/SalesOrder/{id}/recalcShipping",
     *   name="Sales_SalesOrder_recalcShipping")
     * @Method("POST")
     */
    public function recalcShippingAction(SalesOrder $order)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        $uri = $this->orderUrl($order);
        $response = JsonResponse::javascriptRedirect($uri);

        $method = $order->getShippingMethod();
        if (! $method) {
            $this->logError(
                'Cannot determine shipping costs: no shipping method selected.'
            );
            return $response;
        }
        try {
            $order->updateShippingPrice($this->shipmentFactory);
            $this->dbm->flush();
            $this->logNotice(sprintf(
                'Shipping price for method "%s" is $%s.',
                $order->getShippingMethod(),
                number_format($order->getShippingPrice(), 2)
            ));
        } catch (\Exception $ex) {
            $this->logException($ex);
        }
        return $response;
    }

    /**
     * @Route("/Sales/SalesOrder/{id}/recalcTax",
     *   name="Sales_SalesOrder_recalcTax")
     * @Method("POST")
     */
    public function recalcTaxAction(SalesOrder $order)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        /* @var $lookup TaxLookup */
        $lookup = $this->get(TaxLookup::class);
        $lookup->updateTaxRates($order);

        $tax = $order->getTaxAmount();
        $this->dbm->flush();
        $this->logNotice(sprintf(
            'Sales taxes (from %s) are now $%s.',
            $lookup->getProviderName(),
            number_format($tax, 2)
        ));

        $uri = $this->orderUrl($order);
        return JsonResponse::javascriptRedirect($uri);
    }

    /**
     * @Route("/Sales/SalesOrder/{id}/autoPopulateCustomerPartNo",
     *   name="Sales_SalesOrder_autoPopulateCustomerPartNo")
     * @Method("POST")
     */
    public function autoPopulateCustomerPartNo(SalesOrder $order)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        /** @var $populator CustomerPartNoPopulator */
        $populator = $this->get(CustomerPartNoPopulator::class);

        $this->dbm->beginTransaction();
        try {
            $populator->autoPopulate($order);
            $this->dbm->flushAndCommit();
            $this->logNotice("Line item customer part no. has been auto generated");
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            $this->logException($ex);
        }

        $uri = $this->orderUrl($order);
        return JsonResponse::javascriptRedirect($uri);
    }

    /**
     * @Route("/Sales/SalesOrder/{id}/priority",
     *   name="Sales_SalesOrder_priority")
     * @Method("POST")
     */
    public function priorityAction(SalesOrder $order)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        if ($order->hasPriority()) {
            $order->removePriority();
            $set = 'removed';
        } else {
            $order->setPriority();
            $set = 'set';
        }
        $this->dbm->flush();
        $this->logNotice("The priority flag has been $set successfully.");

        $uri = $this->orderUrl($order);
        return JsonResponse::javascriptRedirect($uri);
    }

    /**
     * @Route("/Sales/SalesOrder/{id}", name="Sales_SalesOrder_close")
     * @Method("DELETE")
     */
    public function closeAction(SalesOrder $order)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        $this->dbm->beginTransaction();
        try {
            $order->close();

            $event = new SalesOrderEvent($order);
            $this->dispatchEvent(SalesEvents::ORDER_CLOSED, $event);
            $this->logWarnings($event->getWarnings());

            $this->dbm->flushAndCommit();

            $this->logNotice("Closed $order.");
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            $this->logException($ex);
        }

        return $this->redirectToOrder($order);
    }


    /**
     * @Route("/Sales/SalesOrder/{id}/duplicate",
     *   name="Sales_SalesOrder_duplicate")
     * @Method("POST")
     */
    public function duplicateAction(SalesOrder $order)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        $this->dbm->beginTransaction();
        try {
            $newOrder = clone $order;
            $newOrder->setCreatedBy($this->getCurrentUser());
            $this->dbm->persist($newOrder);
            $this->dbm->flushAndCommit();
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }
        $this->logNotice("This is your new quotation copied from $order.");

        return $this->redirectToOrder($newOrder);
    }

    /**
     * Shows the denied parties that the given order matches.
     *
     * @Route("/Sales/SalesOrder/{id}/deniedParties",
     *   name="Sales_SalesOrder_deniedParties")
     * @Method({"GET", "POST"})
     * @Template("sales/order/denied-parties.html.twig")
     */
    public function deniedPartiesAction(SalesOrder $order, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::CUSTOMER_SERVICE);
        $dps = $this->get(DeniedPartyScreener::class);
        $error = null;
        $dpsResponse = null;
        try {
            $dpsResponse = $dps->screen($order);
        } catch (DeniedPartyException $ex) {
            $error = $ex->getMessage();
        }

        $branch = $order->getCustomerBranch();
        $options = ['validation_groups' => 'deniedParty'];
        $form = $this->createFormBuilder($branch, $options)
            ->add('deniedPartyExemption', TextareaType::class, [
                'label' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Set exemption'])
            ->add('remove', SubmitType::class, ['label' => 'Remove exemption'])
            ->getForm();

        $returnUrl = $this->orderUrl($order);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('remove')->isClicked()) {
                $branch->setDeniedPartyExemption("");
            }
            $this->dbm->flush();
            /* @var $logger SalesLogger */
            $logger = $this->get(SalesLogger::class);
            $msg = $logger->deniedPartyExemption($branch);
            $this->logNotice($msg);
            return $this->redirect($returnUrl);
        }

        return [
            'order' => $order,
            'response' => $dpsResponse,
            'error' => $error,
            'form' => $form->createView(),
            'cancelUri' => $returnUrl,
        ];
    }

    /**
     * Authorize the customer's credit card to pay for this order.
     *
     * This does not charge the card -- it just authorizes it for the
     * amount due.
     *
     * @Route("/Sales/SalesOrder/{id}/authorize",
     *   name="Sales_SalesOrder_authorize")
     * @Template("sales/order/authorize.html.twig")
     */
    public function authorizeAction(SalesOrder $order, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        if ($order->isFullyPaid()) {
            throw $this->badRequest("$order is fully paid");
        } elseif ($order->getCardAuthorization()) {
            throw $this->badRequest("$order is already authorized");
        }

        $cardInfo = new CardAuth($order->getTotalAmountOutstanding());
        $form = $this->createForm(CardAuthType::class, $cardInfo);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $processor SalesOrderPaymentProcessor */
            $processor = $this->get(SalesOrderPaymentProcessor::class);
            $this->dbm->beginTransaction();
            try {
                $cardTrans = $processor->authorizeCard($cardInfo, $order);
                $this->dbm->persist($cardTrans);
                $this->dbm->flushAndCommit();

                if ($order->isQuotation()) {
                    $this->dbm->beginTransaction();
                    $order->convertToOrder();
                    $event = new SalesOrderEvent($order);
                    $this->dispatchEvent(SalesEvents::ORDER_AUTHORIZED, $event);
                    $this->dbm->flushAndCommit();
                }

                return $this->redirectToOrder($order);
            } catch (GatewayException $ex) {
                $this->dbm->rollBack();
                $this->logError($ex->getMessage());
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }

        return [
            'order' => $order,
            'form' => $form->createView(),
            'cancelUri' => $this->orderUrl($order),
        ];
    }

    /**
     * Downloads the packing slip.
     *
     * @Route("/Sales/SalesOrder/{id}/packingSlip",
     *   name="Sales_SalesOrder_packingSlip")
     * @Method("GET")
     */
    public function packingSlipAction(SalesOrder $order)
    {
        $this->denyAccessUnlessGranted([Role::SALES, Role::WAREHOUSE]);
        $generator = $this->get(SalesPdfGenerator::class);
        $pdf = $generator->generatePackingSlip($order);
        return PdfResponse::create($pdf, "$order packing slip.pdf");
    }

    /**
     * Choose the shipper and shipping method for an order.
     *
     * @Route("/sales/salesorder/{id}/shipping/", name="sales_order_shipping")
     * @Template("form/minimal.html.twig")
     */
    public function shippingAction(SalesOrder $order, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::CUSTOMER_SERVICE, Role::WAREHOUSE]);
        if (! $order->containsShippableItems()) {
            throw $this->badRequest("$order has no shippable items");
        }
        $method = $order->getShippingMethod();
        /** @var $form FormInterface */
        $form = $this->createFormBuilder($order)
            ->setAction($this->getCurrentUri())
            ->add('shippingMethod', ShippingMethodType::class, [
                'label' => false,
                'required' => true,
                /* Make the current shipping method available to the
                 * javascript code so it knows which one to pre-select. */
                'attr' => [
                    'class' => 'shipping-method',
                    'data-value' => $method ? $method->getCode() : '',
                ],
            ])
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->flush();
            $url = $this->generateUrl('sales_order_edit', [
                'order' => $order->getId(),
            ]);
            return JsonResponse::javascriptRedirect($url);
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Returns shipment options and rates for $order.
     *
     * @Route("/sales/order/{id}/shipmentOptions/",
     *   name="sales_order_shipmentOptions",
     *   options={"expose"=true})
     * @Method("POST")
     */
    public function shipmentOptionsAction(SalesOrder $order, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::SALES);
        /** @var $shipper Shipper */
        $shipper = $this->needEntityFromRequest(
            Shipper::class, 'shipper', $request);
        $order->setShipper($shipper);
        try {
            $options = $this->shipmentFactory->getShipmentOptions($order);
            return View::create(ShipmentOptionFacade::fromList($options));
        } catch (UpsXmlError $ex) {
            if ($ex->isUserError()) {
                throw $this->badRequest($ex->getMessage());
            }
            throw $ex;
        }
    }
}
