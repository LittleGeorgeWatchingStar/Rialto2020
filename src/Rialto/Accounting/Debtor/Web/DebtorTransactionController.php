<?php

namespace Rialto\Accounting\Debtor\Web;

use DateTime;
use Rialto\Accounting\Debtor\Credit\CreditNote;
use Rialto\Accounting\Debtor\DebtorInvoice;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Accounting\Debtor\DebtorTransactionFactory;
use Rialto\Accounting\Debtor\Email\DebtorTransactionEmail;
use Rialto\Accounting\Debtor\Orm\DebtorTransactionRepository;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\PaymentTransaction\PaymentTransaction;
use Rialto\Accounting\PaymentTransaction\Web\PaymentTransactionController;
use Rialto\Accounting\Web\AccountingRouter;
use Rialto\Database\Orm\EntityList;
use Rialto\Email\Mailable\Web\TextMailableType;
use Rialto\Email\MailerInterface;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderPaymentProcessor;
use Rialto\Sales\SalesPdfGenerator;
use Rialto\Security\Role\Role;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\Response\PdfResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\Validator\Constraints\File;

/**
 * Controller for managing customer transactions.
 */
class DebtorTransactionController extends PaymentTransactionController
{
    /**
     * @var AccountingRouter
     */
    private $router;

    /** @var EngineInterface */
    private $templating;

    protected function init(ContainerInterface $container)
    {
        $this->router = $this->get(AccountingRouter::class);
        $this->templating = $this->getTemplating();
    }

    /**
     * @Route("/debtor/transaction/", name="debtor_transaction_list")
     * @Method("GET")
     * @Template("accounting/debtor/transaction/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted([Role::ACCOUNTING, Role::CUSTOMER_SERVICE]);
        $form = $this->createForm(ListFilterType::class);
        $form->submit($request->query->all());
        /** @var $repo DebtorTransactionRepository */
        $repo = $this->getRepository(DebtorTransaction::class);
        $filters = $form->getData();
        $list = new EntityList($repo, $filters);
        return [
            'list' => $list,
            'form' => $form->createView(),
            'customer' => $filters['customer'],
        ];
    }

    /**
     * @Route("/debtor/transaction/{trans}/", name="debtor_transaction_view")
     * @Method("GET")
     * @Template("accounting/debtor/transaction/view.html.twig")
     */
    public function viewAction(DebtorTransaction $trans)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $ctrl = self::class;
        return [
            'entity' => $trans,
            'captureAction' => "$ctrl::captureAction",
        ];
    }

    /**
     * For editing existing debtor transactions.
     *
     * @Route("/debtor/transaction/{id}/edit/", name="debtor_transaction_edit")
     * @Template("accounting/debtor/transaction/edit.html.twig")
     */
    public function editAction(DebtorTransaction $trans, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $form = $this->createForm(DebtorTransactionType::class, $trans);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->flush();
            return $this->redirectToView($trans);
        }

        return [
            'trans' => $trans,
            'form' => $form->createView(),
            'cancelUri' => $this->viewUrl($trans),
        ];
    }

    private function redirectToView(DebtorTransaction $transaction)
    {
        $url = $this->viewUrl($transaction);
        return $this->redirect($url);
    }

    /**
     * @param DebtorTransaction $transaction
     */
    protected function viewUrl(PaymentTransaction $transaction)
    {
        return $this->router->debtorTransView($transaction);
    }


    /**
     * For allocating credits to a single invoice.
     *
     * @Route("/Debtor/Transaction/{id}/allocate",
     *   name="Debtor_Transaction_allocate")
     * @Template("accounting/debtor/transaction/allocate.html.twig")
     */
    public function allocateAction(DebtorTransaction $invoice, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        if (!$invoice->isInvoice()) {
            throw $this->badRequest("$invoice is not an invoice");
        }

        $options = ['validation_groups' => ['Default', 'invoice']];
        $form = $this->createFormBuilder($invoice, $options)
            ->add('allocations', CollectionType::class, [
                'entry_type' => DebtorAllocationType::class,
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
     * @Route("/Debtor/Transaction/{id}/email",
     *   name="Debtor_Transaction_email")
     * @Template("accounting/debtor/transaction/email.html.twig")
     */
    public function emailAction(DebtorTransaction $debtorTrans, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::ACCOUNTING, Role::SALES, Role::CUSTOMER_SERVICE]);
        $email = new DebtorTransactionEmail($debtorTrans);
        $email->setFrom($this->getDefaultCompany());

        $pdfData = $this->generatePdf($debtorTrans);
        $email->setPdfData($pdfData);

        $email->render($this->templating);
        $builder = $this->createFormBuilder($email);
        $builder->setAction($this->getCurrentUri())
            ->add('to', TextMailableType::class, [
                'multiple' => true,
                'error_bubbling' => true,
            ])
            ->add('body', TextareaType::class, [
                'error_bubbling' => true,
            ])
            ->add('send', SubmitType::class);
        $form = $builder->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->get(MailerInterface::class)->send($email);
            $this->logNotice("Email sent successfully.");
            $uri = $this->viewUrl($debtorTrans);
            return JsonResponse::javascriptRedirect($uri);
        }

        return [
            'creditNote' => $debtorTrans,
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
        ];
    }

    /**
     * @Route("/Debtor/Transaction/{id}/pdf",
     *   name="Debtor_Transaction_pdf")
     */
    public function pdfAction(DebtorTransaction $debtorTrans, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::ACCOUNTING, Role::SALES, Role::CUSTOMER_SERVICE]);
        $pdfData = $this->generatePdf($debtorTrans);
        $filename = $debtorTrans->getSystemType()->getName() .
            $debtorTrans->getSystemTypeNumber() . ".pdf";
        return PdfResponse::create($pdfData, $filename);
    }

    private function generatePdf(DebtorTransaction $debtorTrans)
    {
        /** @var $generator SalesPdfGenerator */
        $generator = $this->get(SalesPdfGenerator::class);
        return $generator->generateDebtorTransactionPdf($debtorTrans);
    }

    /**
     * Deletes the debtor transaction and all associated bank transactions
     * and GL entries.
     *
     * Debtor transactions with associated stock moves cannot be deleted.
     *
     * @Route("/record/Debtor/Transaction/{id}",
     *   name="Debtor_Transaction_delete")
     * @Method("DELETE")
     */
    public function deleteAction(DebtorTransaction $trans, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        if (count($trans->getStockMoves()) > 0) {
            throw $this->badRequest(
                "Cannot delete debtor transactions that have stock moves");
        }
        $returnUri = $this->generateUrl('debtor_transaction_list', [
            'customer' => $trans->getCustomer()->getId(),
        ]);
        return $this->deleteHelper($trans, $returnUri, $request);
    }

    /**
     * @Route("/Debtor/Transaction/{id}/amountAllocated/",
     *   name="Debtor_Transaction_amountAllocated")
     * @Method("POST")
     */
    public function updateAmountAllocatedAction(DebtorTransaction $trans)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $trans->updateAmountAllocated();
        $this->dbm->flush();
        return $this->redirectToView($trans);
    }

    private function uploadForm()
    {
        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('debtor_transaction_create'))
            ->add('file', FileType::class, [
                'constraints' => new File([
                    'maxSize' => '10M',
                    'mimeTypes' => [
                        'text/plain',
                    ],
                ]),
                'label' => 'Upload the transaction .txt file:',
            ])
            ->add('Upload', SubmitType::class)
            ->getForm();
        return $form;
    }

    /**
     * Renders and processes the form for uploading transactions file.
     *
     * @Route("/sales/create-debtorTransaction/upload/", name="debtor_transaction_create")
     * @Template("sales/order/createDebtorTransaction.html.twig")
     */
    public function uploadAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $form = $this->uploadForm();
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $transactionData = $form->get('file')->getData();
                $repo = $this->getRepository(SalesOrder::class);
                $transactionData = CustomerTransaction::createTransaction($transactionData, $repo);
                return ['transactionData' => $transactionData];
            }
        }
        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * Renders and processes the form for listing pending transactions to be approved.
     *
     * @Route("/sales/create-debtorTransaction/approve/", name="approve_transaction", options={"expose"=true})
     * @Template("sales/order/approveTransaction.html.twig")
     */
    public function approveAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $htmlArguments = [];
        if ($request->isMethod('POST')) {
            $transactionList = array();
            $salesOrderRepo = $this->getRepository(SalesOrder::class);
            if (isset($_POST['checkedValue'])) {
                foreach ($request->get('checkedValue') as $transactionData) {
                    list($orderId, $amount, $date) = explode('|', $transactionData);
                    $orders = new EntityList($salesOrderRepo, ['id' => $orderId]);
                    if ($this->transactionsExistFor($orders, $date)) { continue; }
                    $debtorTransaction = $this->createTransaction($orders, $amount, $date);
                    array_push($transactionList, [$debtorTransaction, $transactionData]);
                }
                if (!isset($_POST['approved'])) {
                    $this->dbm->clear();
                    $htmlArguments = ['transactionList' => $transactionList];
                } else {
                    $this->dbm->flush();
                    $this->logNotice("Transaction report recorded successfully.");
                    $htmlArguments = ['transactionList' => $transactionList, 'Done' => 1];
                }
            } else {
                $this->logNotice("No change is made.");
                $this->dbm->clear();
                $form = $this->uploadForm();
                $htmlArguments = $this->render('sales/order/createDebtorTransaction.html.twig', [
                    'form' => $form->createView()
                ]);
            }
        }
        return $htmlArguments;
    }

    private function transactionsExistFor(EntityList $orders, string $date)
    {
        $dateTime = new DateTime($date);
        $debtorTransactionRepo = $this->getRepository(DebtorTransaction::class);
        if ($debtorTransactionRepo) {
            return 0;
        }
        $existing = new EntityList($debtorTransactionRepo, [
            'customer' => $orders->first()->getCustomer(),
            'salesOrder' => $orders->first()
        ]);
        return ($existing->first()->getDate()->format('d/m/y') === $dateTime->format('d/m/y'));
    }

    /**
     * Transaction will be persist into Doctrine DB manager.
     * If transaction is not ready to write into DB, use clear function before flush anything.
     */
    private function createTransaction(EntityList $orders, string $amount, string $date) {
        $transFactory = new DebtorTransactionFactory($this->dbm, $this->dispatcher());
        $customerCredit = new CreditNote($orders->first()->getCustomer());
        $customerCredit->setToAccount(GLAccount::fetchBankCharges());
        $customerCredit->setAmount($amount);
        $customerCredit->setSalesOrder($orders->first());
        $customerCredit->setDate(new DateTime($date));
        $debtorTransaction = $transFactory->createCredit($customerCredit);
        $debtorTransaction->setSubtotalAmount($amount);
        return $debtorTransaction;
    }

    /**
     * @Route("/debtor/transaction/{id}/capture/",
     *   name="debtor_trans_capture")
     * @Template("form/minimal.html.twig")
     *
     * @param DebtorInvoice $invoice
     */
    public function captureAction(DebtorTransaction $invoice, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        if (!$invoice->isInvoice()) {
            throw $this->badRequest("$invoice is not an invoice");
        }
        if (!$invoice->canBeCaptured()) {
            throw $this->badRequest("$invoice has nothing to capture");
        }
        $formAction = $this->generateUrl('debtor_trans_capture', [
            'id' => $invoice->getId(),
        ]);
        $form = $this->createFormBuilder()
            ->setAction($formAction)
            ->add('capture', SubmitType::class, [
                'label' => "Capture card authorization",
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                /** @var SalesOrderPaymentProcessor $processor */
                $processor = $this->get(SalesOrderPaymentProcessor::class);
                $receipt = $processor->processPayment($invoice);
                $invoice->allocateFrom($receipt);
                $this->dbm->flushAndCommit();
            } catch (\UnexpectedValueException $ex) {
                $this->dbm->rollBack();
                $this->logException($ex);
                return $this->redirectToView($invoice);
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            $this->logNotice("Created $receipt.");
            return $this->redirectToView($invoice);
        }

        return [
            'form' => $form->createView(),
        ];
    }
}
