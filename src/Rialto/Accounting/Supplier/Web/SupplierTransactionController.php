<?php

namespace Rialto\Accounting\Supplier\Web;

use Exception;
use FOS\RestBundle\View\View;
use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Account\UsedChequeNumberException;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\PaymentTransaction\PaymentTransaction;
use Rialto\Accounting\PaymentTransaction\Web\PaymentTransactionController;
use Rialto\Accounting\Supplier\Email\SupplierTransactionEmail;
use Rialto\Accounting\Supplier\Email\SupplierTransactionEmailType;
use Rialto\Accounting\Supplier\PaymentRun;
use Rialto\Accounting\Supplier\SupplierPayment;
use Rialto\Accounting\Supplier\SupplierRefund;
use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Accounting\Supplier\SupplierTransactionRepository;
use Rialto\Accounting\Web\AccountingRouter;
use Rialto\Cms\CmsEngine;
use Rialto\Database\Orm\EntityList;
use Rialto\Email\MailerInterface;
use Rialto\Filetype\Pdf\PdfGenerator;
use Rialto\Purchasing\Invoice\SupplierInvoiceZipper;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Purchasing\Supplier\Web\SupplierController;
use Rialto\Security\Role\Role;
use Rialto\Time\Web\DateType;
use Rialto\Web\Response\FileResponse;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\Response\PdfResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller for creating supplier transactions such as payments
 * and refunds.
 */
class SupplierTransactionController extends PaymentTransactionController
{
    /**
     * @var AccountingRouter
     */
    private $router;

    protected function init(ContainerInterface $container)
    {
        $this->router = $this->get(AccountingRouter::class);
    }


    /**
     * @Route("/supplier/transaction/", name="supplier_transaction_list")
     * @Method("GET")
     * @Template("accounting/supplier/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted([Role::ACCOUNTING, Role::PURCHASING]);
        $form = $this->createForm(ListFilterType::class);
        $form->submit($request->query->all());
        /** @var $repo SupplierTransactionRepository */
        $repo = $this->getRepository(SupplierTransaction::class);
        $filters = $form->getData();
        $list = new EntityList($repo, $filters);
        $supplierCtrl = SupplierController::class;
        return [
            'list' => $list,
            'form' => $form->createView(),
            'supplier' => $filters['supplier'],
            'paymentStatus' => "$supplierCtrl::paymentStatus",
        ];
    }

    /**
     * @Route("/supplier/transaction/{trans}/", name="supplier_transaction_view")
     * @Method("GET")
     * @Template("accounting/supplier/view.html.twig")
     */
    public function viewAction(SupplierTransaction $trans)
    {
        $this->denyAccessUnlessGranted([Role::ACCOUNTING, Role::PURCHASING]);
        return ['entity' => $trans];
    }

    /**
     * Manually create a payment to a supplier.
     *
     * @Route("/Accounting/Supplier/{id}/payment", name="Accounting_SupplierPayment_create")
     */
    public function createPaymentAction(Supplier $supplier, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $company = $this->getDefaultCompany();
        $payment = new SupplierPayment($company, $supplier);

        /** @var FormInterface $form */
        $form = $this->createFormBuilder($payment)
            ->add('account', EntityType::class, [
                'class' => BankAccount::class,
                'choice_label' => 'name',
                'label' => 'Bank account',
            ])
            ->add('date', DateType::class)
            ->add('paymentType', ChoiceType::class, [
                'choices' => BankTransaction::getValidPaymentTypes(),
                'label' => 'Payment method'
            ])
            ->add('chequeNumber', IntegerType::class, ['label' => 'Cheque No'])
            ->add('memo', TextType::class)
            ->add('paymentAmount', MoneyType::class, [
                'currency' => Currency::USD,
                'label' => 'Payment amount',
            ])
            ->add('discountAmount', MoneyType::class, [
                'currency' => Currency::USD,
                'label' => 'Discount amount',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $suppTrans = $payment->createPayment($this->dbm);
                $this->dbm->flushAndCommit();
                $this->logNotice(sprintf(
                    'Paid %s $%s.',
                    $supplier->getName(),
                    number_format($payment->getPaymentAmount(), 2)
                ));
                return $this->redirectToView($suppTrans);
            } catch (UsedChequeNumberException $ex) {
                $this->dbm->rollBack();
                $this->logException($ex);
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }

        return $this->render(
            'accounting/supplier/manualPayment.html.twig', [
            'supplier' => $supplier,
            'form' => $form->createView(),
        ]);
    }

    private function redirectToView(SupplierTransaction $transaction)
    {
        $url = $this->viewUrl($transaction);
        return $this->redirect($url);
    }

    /**
     * @param SupplierTransaction $transaction
     */
    protected function viewUrl(PaymentTransaction $transaction)
    {
        return $this->router->supplierTransView($transaction);
    }


    /**
     * Automatically create payments for all unheld supplier invoices.
     *
     * @Route("/Creditor/PaymentRun/", name="Creditor_PaymentRun")
     * @Template("accounting/supplier/paymentRun.html.twig")
     */
    public function paymentRunAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $run = new PaymentRun($this->getDefaultCompany());
        $form = $this->createForm(PaymentRunType::class, $run);
        if ($request->isMethod('post')) {
            $form->add('createPayments', SubmitType::class);
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $repo = $this->getRepository(SupplierTransaction::class);
            $run->loadInvoices($repo);

            if ($form->get('pdf')->isClicked()) {
                return $this->renderPdfResponse($run, true);
            } if ($form->get('zip')->isClicked()) {
                return $this->renderZipResponse($run);
            } elseif ($form->get('createPayments')->isClicked() && $run->hasPayments()) {
                $this->dbm->beginTransaction();
                try {
                    /* Render the response *before* processing so that we see
                     * the correct outstanding amounts. */
                    $pdfResponse = $this->renderPdfResponse($run);
                    $run->processPayments($this->dbm);
                    $this->dbm->flushAndCommit();
                    return $pdfResponse;
                } catch (UsedChequeNumberException $ex) {
                    $this->dbm->rollBack();
                    $this->logException($ex);
                } catch (\Exception $ex) {
                    $this->dbm->rollBack();
                    throw $ex;
                }
            }
        }

        return [
            'form' => $form->createView(),
            'run' => $run,
        ];
    }

    /** @return Response */
    private function renderPdfResponse(PaymentRun $run, $preview = false)
    {
        /** @var $generator PdfGenerator */
        $generator = $this->get(PdfGenerator::class);
        $pdfData = $generator->render(
            'accounting/supplier/paymentRun.tex.twig', [
            'run' => $run,
            'preview' => $preview,
        ]);

        return PdfResponse::create($pdfData, 'PaymentRun.pdf');
    }

    private function renderZipResponse(PaymentRun $run)
    {
        /** @var SupplierInvoiceZipper $invoiceZipper */
        $invoiceZipper = $this->get(SupplierInvoiceZipper::class);

        $invoicesFileContents = $invoiceZipper->zipPaymentRun($run);
        return FileResponse::fromData($invoicesFileContents, 'invoices.zip');
    }

    /**
     * @Route("/Accounting/Supplier/{id}/refund", name="Accounting_SupplierRefund_create")
     */
    public function createRefundAction(Supplier $supplier, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $company = $this->getDefaultCompany();
        $payment = new SupplierRefund($company, $supplier);

        /** @var FormInterface $form */
        $form = $this->createFormBuilder($payment)
            ->add('account', EntityType::class, [
                'class' => BankAccount::class,
                'choice_label' => 'name',
                'label' => 'Bank account',
            ])
            ->add('date', DateType::class)
            ->add('paymentType', ChoiceType::class, [
                'choices' => BankTransaction::getValidPaymentTypes(),
                'label' => 'Payment method'
            ])
            ->add('chequeNumber', IntegerType::class, [
                'label' => 'Cheque No',
                'required' => false,
            ])
            ->add('memo', TextType::class)
            ->add('refundAmount', MoneyType::class, [
                'currency' => Currency::USD,
                'label' => 'Refund amount',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $suppTrans = $payment->createRefund($this->dbm);
                $this->dbm->flushAndCommit();
                $this->logNotice(sprintf(
                    'Refunded $%s from %s.',
                    number_format($payment->getRefundAmount(), 2),
                    $supplier->getName()
                ));
                return $this->redirectToView($suppTrans);
            } catch (Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }

        return $this->render(
            'accounting/supplier/manualRefund.html.twig', [
            'supplier' => $supplier,
            'form' => $form->createView(),
        ]);
    }

    /**
     * List unsettled invoices and update which ones are held.
     *
     * A "held" invoice is one that will not be paid automatially.
     *
     * @Route("/Creditor/InvoiceHolds/", name="Creditor_InvoiceHolds")
     * @Template("accounting/supplier/listHolds.html.twig")
     */
    public function listHoldsAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $filters = [
            'startDate' => new \DateTime('-3 months'),
            'endDate' => new \DateTime(),
            'payday' => 'Monday',
        ];
        $options = ['method' => 'get', 'csrf_protection' => false];
        $form = $this->createNamedBuilder(null, $filters, $options)
            ->add('startDate', DateType::class)
            ->add('endDate', DateType::class)
            ->add('payday', ChoiceType::class, [
                'choices' => [
                    'Monday' => 'Monday',
                    'Tuesday' => 'Tuesday',
                    'Wednesday' => 'Wednesday',
                    'Thursday' => 'Thursday',
                    'Friday' => 'Friday',
                ],
            ])
            ->add('filter', SubmitType::class)
            ->getForm();
        /* @var $form FormInterface */
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $filters = $form->getData();
        }
        /** @var $repo SupplierTransactionRepository */
        $repo = $this->getRepository(SupplierTransaction::class);
        $invoices = $repo->findInvoicesForHolds($filters['startDate'], $filters['endDate']);

        $payDates = [];
        $totals = [];
        $grandTotals = [];
        $index = [];
        foreach ($invoices as $invoice) {
            $supplier = $invoice->getSupplier();
            $sid = $supplier->getId();
            $index[$sid][] = $invoice;
            $payDate = $invoice->getPaymentDate($filters['payday']);
            $dateStr = $payDate->format('Y-m-d');
            $payDates[$dateStr] = $payDate;
            if (!isset($totals[$sid][$dateStr])) {
                $totals[$sid][$dateStr] = 0;
            }
            if (!isset($grandTotals[$dateStr])) {
                $grandTotals[$dateStr] = 0;
            }
            $totals[$sid][$dateStr] += $invoice->getAmountUnallocated();
            $grandTotals[$dateStr] += $invoice->getAmountUnallocated();
        }

        ksort($payDates); // sort by date
        return [
            'index' => $index,
            'form' => $form->createView(),
            'payday' => $filters['payday'],
            'payDates' => $payDates,
            'totals' => $totals,
            'grandTotals' => $grandTotals,
        ];
    }

    /**
     * @Route("/record/Accounting/SupplierTransaction/{id}/hold",
     *   name="Accounting_SupplierTransaction_hold")
     * @Method("PUT")
     */
    public function holdAction(SupplierTransaction $trans, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        if (!$trans->isInvoice()) {
            throw $this->badRequest("Only invoices can be held");
        } elseif ($trans->isSettled()) {
            throw $this->badRequest("Transaction is settled");
        } elseif ($trans->getAmountUnallocated() <= 0) {
            throw $this->badRequest("Transaction is fully allocated");
        }
        $hold = (bool) $request->get('hold');
        $trans->setHold($hold);
        $this->dbm->flush();
        return View::create($trans->isHold());
    }

    /**
     * Deletes the supplier transaction and all associated bank transactions
     * and GL entries.
     *
     * @Route("/record/Accounting/SupplierTransaction/{id}",
     *   name="Accounting_SupplierTransaction_delete")
     * @Method("DELETE")
     */
    public function deleteAction(SupplierTransaction $trans, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $returnUri = $this->generateUrl('supplier_transaction_list', [
            'supplier' => $trans->getSupplier()->getId(),
        ]);
        return $this->deleteHelper($trans, $returnUri, $request);
    }

    /**
     * Deletes the supplier transaction and all associated bank transactions
     * and GL entries.
     *
     * @Route("/Accounting/SupplierTransaction/{id}/date",
     *   name="Accounting_SupplierTransaction_changeDate")
     */
    public function changeDateAction(SupplierTransaction $trans, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        return $this->changeDateHelper($trans, $request);
    }


    /**
     * @Route("/Accounting/SupplierTransaction/{id}/allocate",
     *   name="Accounting_SupplierTransaction_allocate")
     * @Template("accounting/supplier/allocate.html.twig")
     */
    public function allocateAction(SupplierTransaction $invoice, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        if (!$invoice->isInvoice()) {
            throw $this->badRequest("$invoice is not an invoice");
        }

        /** @var FormInterface $form */
        $form = $this->createFormBuilder($invoice)
            ->add('allocations', CollectionType::class, [
                'entry_type' => SupplierAllocationType::class,
                'entry_options' => ['invoice' => $invoice],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Save changes',
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->flush();
            $this->logNotice(ucfirst("$invoice updated."));
            return $this->redirect($this->getCurrentUri());
        }

        return [
            'invoice' => $invoice,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/Accounting/SupplierTransaction/{id}/email/",
     *   name="Accounting_SupplierTransaction_email")
     * @Template("accounting/supplier/email.html.twig")
     */
    public function emailAction(SupplierTransaction $payment, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        if (!$payment->isCredit()) {
            throw $this->badRequest("$payment is not a payment");
        }
        $company = $this->getDefaultCompany();
        $sender = $this->getCurrentUser();
        $email = new SupplierTransactionEmail($payment);
        $email->setFrom($sender);
        $body = $this->get(CmsEngine::class)
            ->render('accounting.supplier_payment_email', [
                'date' => $payment->getDate()->format('F j, Y'),
                'amount' => number_format(-$payment->getTotalAmount(), 2),
                'chequeNo' => $email->getChequeNo(),
                'supplier' => $payment->getSupplier(),
                'company' => $company->getShortName(),
                'sender' => $sender->getName(),
            ]);
        $email->setBody($body);
        $email->setSubject(sprintf('Payment from %s', $company->getShortName()));

        $form = $this->createForm(SupplierTransactionEmailType::class, $email);
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->get(MailerInterface::class)->send($email);
                $this->logNotice("Email sent successfully.");

                $uri = $this->viewUrl($payment);
                $uri = $this->getReturnUri($uri);
                return JsonResponse::javascriptRedirect($uri);
            } else {
                return JsonResponse::fromInvalidForm($form);
            }
        }

        return [
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
        ];
    }

    /**
     * @Route("/Creditor/Transaction/{id}/amountAllocated/",
     *   name="Creditor_Transaction_amountAllocated")
     * @Method("POST")
     */
    public function updateAmountAllocatedAction(SupplierTransaction $trans)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $trans->updateAmountAllocated();
        $this->dbm->flush();
        $this->logNotice("Amount allocated for $trans updated.");
        return $this->redirectToView($trans);
    }
}
