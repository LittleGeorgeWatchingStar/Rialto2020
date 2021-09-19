<?php

namespace Rialto\Purchasing\Invoice\Web;

use Doctrine\ORM\EntityRepository;
use Exception;
use Gumstix\Storage\StorageException;
use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Accounting\Supplier\SupplierTransactionRepository;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\EntityList;
use Rialto\Email\MailerInterface;
use Rialto\Purchasing\Invoice\Command\UploadSupplierInvoiceFileCommand;
use Rialto\Purchasing\Invoice\Command\UploadSupplierInvoiceFileHandler;
use Rialto\Purchasing\Invoice\Email\SupplierInvoiceEmail;
use Rialto\Purchasing\Invoice\Orm\SupplierInvoiceRepository;
use Rialto\Purchasing\Invoice\Reader\Email\Web\SupplierEmailController;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Purchasing\Invoice\SupplierInvoiceFilesystem;
use Rialto\Purchasing\Invoice\SupplierInvoiceItem;
use Rialto\Purchasing\Invoice\SupplierPOItemSplit;
use Rialto\Purchasing\Invoice\SupplierPOItemsSplitSolo;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Order\PurchaseOrderItem;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Purchasing\Receiving\GoodsReceivedItem;
use Rialto\Purchasing\Receiving\GoodsReceivedItemRepository;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\Role\Role;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for editing and approving invoices from suppliers.
 *
 * You can also create invoices manually, but that tends to be done
 * automatically by the supplier email reader; @see SupplierEmailController.
 */
class SupplierInvoiceController extends RialtoController
{
    /**
     * @Route("/purchasing/supplier-invoice/", name="supplier_invoice_list")
     * @Method("GET")
     * @Template("purchasing/invoice/invoice-list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $form = $this->createForm(SupplierInvoiceListFilter::class);
        $form->submit($request->query->all());
        $list = new EntityList($this->repo(), $form->getData());
        if ($request->get('_show_unique') && $list->total() === 1) {
            return $this->redirectToView($list->first());
        }
        return [
            'invoices' => $list,
            'form' => $form->createView(),
        ];
    }

    /**
     * @return SupplierInvoiceRepository|EntityRepository
     */
    private function repo()
    {
        return $this->getRepository(SupplierInvoice::class);
    }


    /**
     * @Route("/purchasing/supplier-invoice/{id}/", name="supplier_invoice_view")
     * @Method("GET")
     * @Template("purchasing/invoice/invoice-view.html.twig")
     */
    public function viewAction(SupplierInvoice $invoice, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        return ['entity' => $invoice,
            'request' => $request];
    }

    /**
     * Renders all of the supplier invoices that are awaiting approval.
     *
     * @Route("/Purchasing/SupplierInvoice/approve",
     *   name="Purchasing_SupplierInvoice_approvalList")
     * @Method("GET")
     * @Template("purchasing/invoice/invoice-approvalList.html.twig")
     */
    public function approvalListAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $filters = $request->query->all();
        $filters['approved'] = 'no';
        $invoices = $this->repo()->findByFilters($filters);
        return [
            'invoices' => $invoices,
        ];
    }

    /**
     * For approving a supplier invoice.
     *
     * When an invoice is approved, the corresponding accounting transaction
     * is recorded.
     *
     * @Route("/Purchasing/SupplierInvoice/{id}/approve",
     *   name="Purchasing_SupplierInvoice_approve",
     *   options={"expose"=true})
     * @Template("purchasing/invoice/invoice-approve.html.twig")
     */
    public function approveAction(SupplierInvoice $invoice, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        if ($invoice->isApproved()) {
            $transactions = $this->findExistingTransactions($invoice);
            return $this->render("purchasing/supplier/existing-transactions.html.twig", [
                'invoice' => $invoice,
                'transactions' => $transactions,
            ]);
        }
        $this->preSelectMatches($invoice);
        $options = [
            'validation_groups' => ['Default', 'approval']
        ];
        $form = $this->createForm(SupplierInvoiceApprovalType::class, $invoice, $options);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $sysType = SystemType::fetchPurchaseInvoice($this->dbm);

            $this->dbm->beginTransaction();
            try {
                $suppTrans = $invoice->approve($sysType, $this->getDefaultCompany());

                $this->dbm->persist($suppTrans);

                /* TODO: getGLEntries requires flushed Transactions to work
                properly at the moment. Remove this flush when the method no
                longer uses the deprecated ErpDbManager.
                */
                $this->dbm->flush();

                if (count($suppTrans->getGLEntries()) == 0) {
                    assertion($suppTrans->getTotalAmount() == 0);
                }
                $this->dbm->flushAndCommit();
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            return new Response(sprintf(
                '<a href="%s" target="_blank">Created %s</a>',
                $this->generateUrl('supplier_transaction_view', [
                    'trans' => $suppTrans->getId(),
                ]),
                strtolower($suppTrans->getLabel())
            ));
        }

        return [
            'invoice' => $invoice,
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
        ];
    }

    /** @return SupplierTransaction[] */
    private function findExistingTransactions(SupplierInvoice $invoice)
    {
        /** @var $repo SupplierTransactionRepository */
        $repo = $this->getRepository(SupplierTransaction::class);
        return $repo->findByInvoice($invoice);
    }

    /**
     * Attempt to match GRN items to invoice items so that the checkboxes
     * on the form appear pre-selected.
     */
    private function preSelectMatches(SupplierInvoice $invoice)
    {
        /** @var $repo GoodsReceivedItemRepository */
        $repo = $this->getRepository(GoodsReceivedItem::class);
        foreach ($invoice->getItems() as $item) {
            if (!$item->isRegularItem()) {
                continue;
            }

            $grnItems = $repo->findBySupplierInvoiceItem($item);
            if ($this->getTotalQtyReceived($grnItems) == $item->getQtyInvoiced()) {
                foreach ($grnItems as $grnItem) {
                    /* Skip GRN items that are already matched */
                    if ($grnItem->getInvoiceItem()) continue;
                    $item->addGrnItem($grnItem);
                }
            }
        }
    }

    private function getTotalQtyReceived(array $grnItems)
    {
        $total = 0;
        foreach ($grnItems as $grnItem) {
            /* Skip moves that are already matched */
            if ($grnItem->getInvoiceItem()) continue;
            $total += $grnItem->getQtyReceived();
        }
        return $total;
    }

    /**
     * For undoing an approval.
     *
     * @Route("/Purchasing/SupplierInvoice/{id}/unapprove/",
     *   name="Purchasing_SupplierInvoice_unapprove")
     * @Method("PUT")
     */
    public function unapproveAction(SupplierInvoice $invoice)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        if (count($this->findExistingTransactions($invoice)) > 0) {
            $msg = "You must delete existing supplier transactions before $invoice can be unapproved.";
            $this->logError(ucfirst($msg));
            return $this->redirectToView($invoice);
        }
        $this->dbm->beginTransaction();
        try {
            $invoice->unapprove();
            $this->dbm->flushAndCommit();
        } catch (Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }

        $this->logNotice("$invoice has been unapproved successfully.");
        return $this->redirectToView($invoice);
    }

    private function redirectToView(SupplierInvoice $invoice)
    {
        return $this->redirectToRoute('supplier_invoice_view', [
            'id' => $invoice->getId(),
        ]);
    }

    /**
     * Manually enter a new supplier invoice for $supplier.
     *
     * This is typically used for suppliers who don't send POs; eg. lawyers, etc.
     *
     * @Route("/Purchasing/Supplier/{id}/invoice/",
     *   name="Purchasing_SupplierInvoice_fromSupplier")
     * @Template("purchasing/invoice/invoice-create.html.twig")
     */
    public function fromSupplierAction(Supplier $supplier, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $invoice = new SupplierInvoice($supplier);
        $item = new SupplierInvoiceItem();
        $item->setLineNumber(1);
        $item->setQtyOrdered(1);
        $item->setQtyInvoiced(1);
        $invoice->addItem($item);
        $cancelUri = $this->generateUrl('supplier_view', [
            'supplier' => $supplier->getId(),
        ]);
        return $this->processForm($invoice, $request, $cancelUri, 'created');
    }

    /**
     * Manually enter a new supplier invoice from a purchase order.
     *
     * This is not usually used -- instead, invoices are imported via
     * the email reader.
     *
     * @Route("/Purchasing/PurchaseOrder/{id}/invoice/",
     *   name="Purchasing_SupplierInvoice_fromPO")
     * @Template("purchasing/invoice/invoice-create.html.twig")
     */
    public function fromPOAction(PurchaseOrder $po, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $invoice = SupplierInvoice::fromPurchaseOrder($po);
        foreach ($po->getItems() as $idx => $poItem) {
            $lineItem = SupplierInvoiceItem::fromStockProducer($poItem);
            $lineItem->setLineNumber($idx + 1);
            $invoice->addItem($lineItem);
        }
        $cancelUri = $this->getReturnUri($this->generateUrl('index'));
        return $this->processForm($invoice, $request, $cancelUri, 'created');
    }

    /**
     * Edit an existing supplier invoice.
     *
     * @Route("/Purchasing/SupplierInvoice/{id}", name="Purchasing_SupplierInvoice_edit")
     * @Template("purchasing/invoice/invoice-edit.html.twig")
     */
    public function editAction(SupplierInvoice $invoice, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $cancelUri = $this->generateUrl('supplier_invoice_view', [
            'id' => $invoice->getId(),
        ]);
        return $this->processForm($invoice, $request, $cancelUri);
    }

    private function processForm(
        SupplierInvoice $invoice,
        Request $request,
        $cancelUri,
        $updated = 'updated')
    {
        $form = $this->createForm(SupplierInvoiceType::class, $invoice);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $invoice->prepare();
            $this->dbm->persist($invoice);
            $this->dbm->flush();
            $this->logNotice(ucfirst("$invoice $updated successfully."));
            return $this->redirectToView($invoice);
        }

        return [
            'invoice' => $invoice,
            'form' => $form->createView(),
            'cancelUri' => $cancelUri,
        ];
    }

    /**
     * @Route("/Purchasing/SupplierInvoice/{id}/upload/",
     *     name="Purchasing_SupplierInvoice_upload")
     */
    public function uploadInvoiceFileAction(SupplierInvoice $invoice,
                                            Request $request,
                                            UploadSupplierInvoiceFileHandler $handler)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $returnUri = $this->generateUrl('supplier_invoice_view', [
            'id' => $invoice->getId(),
        ]);

        $command = UploadSupplierInvoiceFileCommand::forInvoice($invoice);

        $form = $this->createForm(UploadSupplierInvoiceFileType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('uploadedFile')->getData();
            $command->filename = $file->getClientOriginalName() ?:
                (md5(uniqid()) . $file->guessClientExtension() ?: '');
            $fileObject = $file->openFile();
            $command->filedata = $fileObject->fread($file->getSize());

            $handler->handle($command);

            return $this->redirect($returnUri);
        }

        return $this->render('purchasing/invoice/invoice-upload.html.twig', [
            'invoice' => $invoice,
            'form' => $form->createView(),
            'cancelUri' => $returnUri,
        ]);
    }

    /**
     * @Route("/record/Purchasing/SupplierInvoice/{id}/",
     *   name="Purchasing_SupplierInvoice_delete")
     * @Method("DELETE")
     */
    public function deleteAction(SupplierInvoice $invoice)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        if ($invoice->isApproved()) {
            throw $this->badRequest("Cannot delete an approved invoice.");
        }

        $supplier = $invoice->getSupplier();
        $ref = $invoice->getSupplierReference();
        $this->dbm->remove($invoice);
        $this->dbm->flush();

        $this->logNotice("Deleted invoice $ref from $supplier.");
        $returnTo = $this->generateUrl('Purchasing_SupplierInvoice_approvalList');
        return $this->redirect($returnTo);
    }

    /**
     * Download the original invoice file that the supplier sent to us.
     *
     * This is usually a PDF or similar.
     *
     * @Route("/Purchasing/SupplierInvoice/{id}/download",
     *   name="Purchasing_SupplierInvoice_download")
     * @Method("GET")
     */
    public function downloadAction(SupplierInvoice $invoice,
                                   SupplierInvoiceFilesystem $filesystem)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        if (!$invoice->getFilename()) {
            throw $this->badRequest();
        }
        return $this->downloadFileAction(
            $invoice->getSupplier(),
            $invoice->getFilename(),
            $filesystem);
    }

    /**
     * Like downloadAction() above, but for new invoices that haven't
     * been saved yet.
     *
     * @Route("/Purchasing/Supplier/{id}/invoice/download/{filename}",
     *   name="Purchasing_SupplierInvoice_downloadFile")
     * @Method("GET")
     */
    public function downloadFileAction(Supplier $supplier, string $filename,
                                       SupplierInvoiceFilesystem $filesystem)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        try {
            $data = $filesystem->getFileContents($supplier, $filename);
        } catch (StorageException $ex) {
            throw $this->notFound($ex->getMessage(), $ex);
        }
        return FileResponse::fromData($data, $filename);
    }

    /**
     * @Route("/Purchasing/SupplierInvoice/{id}/email/",
     *     name="Purchasing_SupplierInvoice_email")
     * @Template("core/form/dialogForm.html.twig")
     */
    public function emailAction(SupplierInvoice $invoice,
                                Request $request,
                                MailerInterface $mailer)
    {
        $this->denyAccessUnlessGranted([Role::PURCHASING, Role::STOCK]);

        $sender = $this->getCurrentUser();
        $email = new SupplierInvoiceEmail($sender);
        $email->setSubject("RE: PO " . $invoice->getPurchaseOrder()->getId() . " from " . $invoice->getSupplier()->getName() . " shipped on " . $invoice->getInvoiceDate()->format('Y-m-d'));
        $form = $this->createForm(SupplierInvoiceEmailType::class, $email, [
            'invoice' => $invoice,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $mailer->send($email);

                $this->logNotice('Email sent successfully.');
                $uri = $this->generateUrl('Purchasing_SupplierInvoice_approvalList');
                return JsonResponse::javascriptRedirect($uri);
            } else {
                return JsonResponse::fromInvalidForm($form);
            }
        }

        return [
            'email' => $email,
            'formAction' => $this->getCurrentUri(),
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/Purchasing/SupplierInvoiceItem/{id}/split/",
     *     name="Purchasing_SupplierInvoiceItem_split")
     * @Template("core/form/splitItemsForm.html.twig")
     */
    public function splitAction(SupplierInvoiceItem $item, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::PURCHASING, Role::STOCK]);

        $purchaseOrder = $item->getPurchaseOrder();
        $poItemSplits = new SupplierPOItemSplit();

        if ($purchaseOrder == NULL){
            throw $this->badRequest("No purchase order to split.");
        }
        $poItems = $purchaseOrder->getItems();

        $itemId = $item->getSupplierInvoice()->getId();

        $attachments = array_map(function (StockProducer $orderItem) {
            $temp = new SupplierPOItemsSplitSolo();
            $temp->setPOItem($orderItem);
            return $temp;
        }, $poItems);
        $poItemSplits->setAttachments($attachments);

        $form = $this->createForm(SupplierPOItemSplitType::class, $poItemSplits);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->dbm->beginTransaction();
            try {
                $this->splitInvoiceAccordingToPurchaseOrder($form, $item);
                $this->dbm->flushAndCommit();
                $this->logNotice('Item splitted successfully.');
                $url = $this->generateUrl('Purchasing_SupplierInvoice_edit', ['id' => $itemId]);
                return $this->redirect($url);
            } catch (Exception $e) {
                $this->dbm->rollBack();
                throw $e;
            }
        }

        return [
            'item' => $item,
            'po' => $purchaseOrder,
            'poD' => $poItems,
            'form' => $form->createView(),
        ];
    }


    public function splitInvoiceAccordingToPurchaseOrder(formInterface $form, SupplierInvoiceItem $item){
        $purchaseOrder = $item->getPurchaseOrder();
        $poItems = $purchaseOrder->getItems();
        $subForms = $form->get("attachments");
        $count = 0;

        foreach ($subForms as $sf) {
            $checkValue = $sf->get("splitToThis");
            if ($checkValue) {
                $count++;
            }
        }

        if ($count != 0) {
            $setUnitCost = $item->getUnitCost() / $count;
            $setExtendedCost = $item->getExtendedCost() / $count;

            foreach ($subForms as $key => $sf) {
                $checkValue = $sf->get("splitToThis");

                if ($checkValue) {
                    $newInvoiceItem = new SupplierInvoiceItem();
                    $newInvoiceItem->setUnitCost($setUnitCost);
                    $newInvoiceItem->setExtendedCost($setExtendedCost);
                    $newInvoiceItem->setSupplierReference($item->getSupplierReference());
                    $newInvoiceItem->setLineNumber($item->getLineNumber());
                    $newInvoiceItem->setSupplier($item->getSupplier());
                    $newInvoiceItem->setDescription($poItems[$key]->getDescription());
                    $newInvoiceItem->setQtyInvoiced($item->getQtyInvoiced());
                    $newInvoiceItem->setQtyOrdered($poItems[$key]->getQtyOrdered());
                    $newInvoiceItem->setSupplierInvoice($item->getSupplierInvoice());
                    $this->dbm->persist($newInvoiceItem);
                }

            }
            $this->dbm->remove($item);
        }
    }
}
