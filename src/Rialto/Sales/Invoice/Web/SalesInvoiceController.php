<?php

namespace Rialto\Sales\Invoice\Web;

use Exception;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Printing\Printer\PrinterException;
use Rialto\Sales\Invoice\Label\EciaLabelManager;
use Rialto\Sales\Invoice\SalesInvoice;
use Rialto\Sales\Invoice\SalesInvoiceProcessor;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderInterface;
use Rialto\Sales\Order\SalesOrderPaymentProcessor;
use Rialto\Sales\SalesPdfGenerator;
use Rialto\Sales\SalesPrintManager;
use Rialto\Security\Role\Role;
use Rialto\Shipping\Export\DeniedPartyScreener;
use Rialto\Stock\Publication\Orm\PublicationRepository;
use Rialto\Stock\Publication\Publication;
use Rialto\Stock\Publication\PublicationPrintManager;
use Rialto\Stock\Publication\UploadPublication;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\Response\PdfResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * The page for invoicing and shipping sales orders.
 */
class SalesInvoiceController extends RialtoController
{
    /** @var SalesPrintManager */
    private $salesPrintManager;

    /** @var EciaLabelManager */
    private $eciaLabelManager;

    /** @var PublicationPrintManager */
    private $pubPrintManager;

    /** @var SalesOrderPaymentProcessor */
    private $paymentProcessor;

    /** @var SalesInvoiceProcessor */
    private $invoiceProcessor;

    /** @var SalesPdfGenerator */
    private $pdfGenerator;

    protected function init(ContainerInterface $container)
    {
        $this->salesPrintManager = $this->get(SalesPrintManager::class);
        $this->eciaLabelManager = $this->get(EciaLabelManager::class);
        $this->pubPrintManager = $this->get(PublicationPrintManager::class);
        $this->paymentProcessor = $this->get(SalesOrderPaymentProcessor::class);
        $this->invoiceProcessor = $this->get(SalesInvoiceProcessor::class);
        $this->pdfGenerator = $this->get(SalesPdfGenerator::class);
    }

    /**
     * @Route("/sales/order/{id}/invoice", name="sales_order_invoice")
     * @Route("/Sales/SalesOrder/{id}/invoice", name="Sales_SalesOrder_invoice")
     * @Template("sales/invoice/invoice.html.twig")
     */
    public function invoiceAction(SalesOrder $salesOrder, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::SHIPPING);

        if ($salesOrder->isCompleted()) {
            throw $this->badRequest("Cannot invoice a completed sales order");
        }
        $invoice = new SalesInvoice($salesOrder);

        /** @var $dps DeniedPartyScreener */
        $dps = $this->get(DeniedPartyScreener::class);
        $invoice->setDeniedPartyScreener($dps);

        $form = $this->createForm(SalesInvoiceType::class, $invoice);

        $submitDisabled = true;
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->get('downloadPackingSlip')) {
                return $this->downloadPackingSlip($invoice);
            } elseif ($request->get('downloadCommercialInvoice')) {
                $pdf = $this->pdfGenerator->generateCommercialInvoicePdf($invoice);
                return PdfResponse::create($pdf, 'commercial-invoice.pdf');
            } elseif ($request->get('printPackingSlip')) {
                $this->printPackingSlip($invoice);
            } elseif (($pubId = $request->get('printPublication'))) {
                $this->printPublication($pubId);
            } elseif ('ecia' == $request->get('labels')) {
                $this->printEciaLabels($invoice);
            } elseif ($request->get('processInvoice')) {
                try {
                    $this->processPayment($invoice);
                    $debtorTrans = $this->processInvoice($invoice);
                    return $this->redirectToRoute('debtor_transaction_view', [
                        'trans' => $debtorTrans->getId(),
                    ]);
                } catch (Exception $ex) {
                    $this->logException($ex);
                }
            }
            $submitDisabled = false;
        }

        return [
            'invoice' => $invoice,
            'form' => $form->createView(),
            'submitDisabled' => $submitDisabled,
            'cancelUri' => $this->generateUrl('sales_order_view', [
                'order' => $salesOrder->getId(),
            ]),
            'publications' => $this->loadPublications($salesOrder),
        ];
    }

    private function printPackingSlip(SalesInvoice $invoice)
    {
        $this->salesPrintManager->printPackingSlip($invoice);
        $order = $invoice->getSalesOrder();
        $this->logNotice("Printed packing slip for $order.");
    }

    private function printEciaLabels(SalesInvoice $invoice)
    {
        try {
            $this->eciaLabelManager->printLabels($invoice);
            $this->logNotice("Printed ECIA unit labels.");
        } catch (PrinterException $ex) {
            $this->logException($ex);
        }
    }

    private function printPublication($pubId)
    {
        /** @var $pub UploadPublication */
        $pub = $this->dbm->find(Publication::class, $pubId);
        assertion($pub instanceof UploadPublication);

        try {
            $this->pubPrintManager->printNow($pub);
            $this->logNotice("Printed '$pub'.");
        } catch (PrinterException $ex) {
            $this->logException($ex);
        }
    }

    private function processPayment(SalesInvoice $invoice)
    {
        $this->dbm->beginTransaction();
        try {
            $this->paymentProcessor->processPayment($invoice);
            $this->dbm->flushAndCommit();
        } catch (Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }
    }

    /**
     * @param SalesInvoice $invoice
     * @return DebtorTransaction
     */
    private function processInvoice(SalesInvoice $invoice)
    {
        $this->dbm->beginTransaction();
        try {
            $debtorTrans = $this->invoiceProcessor->processInvoice($invoice);
            $this->dbm->persist($debtorTrans);
            $this->dbm->flushAndCommit();
        } catch (Exception $ex) {
            $this->dbm->rollBack();
            $this->logWarning('Invoice processing aborted.');
            throw $ex;
        }
        return $debtorTrans;
    }

    /** @return Publication[] */
    private function loadPublications(SalesOrder $order)
    {
        /** @var $repo PublicationRepository */
        $repo = $this->getRepository(Publication::class);
        return $repo->findBySalesOrder($order);
    }

    private function downloadPackingSlip(SalesOrderInterface $invoice)
    {
        $pdf = $this->pdfGenerator->generatePackingSlip($invoice);
        $filename = "packing slip for order {$invoice->getOrderNumber()}.pdf";
        return FileResponse::fromData($pdf, $filename, "application/pdf");
    }
}
