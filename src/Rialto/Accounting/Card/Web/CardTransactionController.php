<?php

namespace Rialto\Accounting\Card\Web;

use Rialto\Accounting\Card\CardTransaction;
use Rialto\Accounting\Card\Orm\CardTransactionRepository;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Accounting\Debtor\DebtorTransactionFactory;
use Rialto\Accounting\Debtor\Refund\CardRefund;
use Rialto\Accounting\Debtor\Refund\RefundDataSource;
use Rialto\Database\Orm\EntityList;
use Rialto\Payment\GatewayException;
use Rialto\Payment\PaymentGateway;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for working with credit card transactions.
 */
class CardTransactionController extends RialtoController
{
    /**
     * @Route("/accounting/card-transaction/", name="card_trans_list")
     * @Method("GET")
     * @Template("accounting/cardtrans/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted([Role::ACCOUNTING, Role::CUSTOMER_SERVICE]);
        /** @var CardTransactionRepository $repo */
        $repo = $this->getRepository(CardTransaction::class);
        $list = new EntityList($repo, $request->query->all());
        return ['list' => $list];
    }

    /**
     * @Route("/accounting/card-transaction/{id}/", name="card_trans_view")
     * @Method("GET")
     * @Template("accounting/cardtrans/view.html.twig")
     */
    public function viewAction(CardTransaction $cardTrans)
    {
        $this->denyAccessUnlessGranted([Role::ACCOUNTING, Role::CUSTOMER_SERVICE]);
        $ctrl = self::class;
        return [
            'trans' => $cardTrans,
            'voidAction' => "$ctrl::voidAction",
        ];
    }

    /**
     * Voids an authorized, uncharged card transaction.
     *
     * @Route("/Accounting/CardTransaction/{id}/void/",
     *   name="Accounting_CardTransaction_void")
     * @Template("form/minimal.html.twig")
     */
    public function voidAction(CardTransaction $cardTrans, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::ACCOUNTING, Role::CUSTOMER_SERVICE]);
        if ($cardTrans->isCaptured()) {
            throw $this->badRequest("$cardTrans has already been captured");
        }

        $form = $this->createFormBuilder()
            ->setAction($this->generateUrl('Accounting_CardTransaction_void', [
                'id' => $cardTrans->getId(),
            ]))
            ->add('void', SubmitType::class, [
                'attr' => ['onclick' => "return confirm('Void $cardTrans?');"],
            ])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $gateway = $this->getPaymentGateway();
            try {
                $gateway->void($cardTrans);
            } catch (GatewayException $ex) {
                if ($ex->isTransactionNotFound()) {
                    $cardTrans->setVoid(true);
                    $this->logWarning($ex->getMessage());
                } else {
                    throw $ex;
                }
            }
            $this->dbm->flush();

            $this->logNotice("Voided $cardTrans successfully.");
            return $this->redirectToView($cardTrans);
        }
        return [
            'form' => $form->createView(),
        ];
    }

    private function redirectToView(CardTransaction $trans)
    {
        $url = $this->viewUrl($trans);
        return $this->redirect($url);
    }

    private function viewUrl(CardTransaction $trans)
    {
        return $this->generateUrl('card_trans_view', [
            'id' => $trans->getId(),
        ]);
    }

    /**
     * Refunds the card transaction by attempting to void it.
     *
     * @Route("/Accounting/CardTransaction/{id}/refund",
     *   name="Accounting_CardTransaction_refund")
     * @Method("POST")
     */
    public function refundAction(CardTransaction $cardTrans)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        if (!$cardTrans->isCaptured()) {
            throw $this->badRequest("$cardTrans has not been charged");
        } elseif ($cardTrans->isVoid()) {
            throw $this->badRequest("$cardTrans is void");
        }

        $gateway = $this->getPaymentGateway();
        $this->dbm->beginTransaction();
        try {
            $gateway->void($cardTrans);
            $this->logNotice("Voided $cardTrans.");
        } catch (GatewayException $ex) {
            if ($ex->isTransactionNotFound()) {
                $cardTrans->setSettled(true);
                $this->dbm->flushAndCommit();
                $this->logWarning("$cardTrans has already been settled, " .
                    "so you must issue a refund.");
                $url = $this->generateUrl('Accounting_CardTransaction_creditRefund', [
                    'id' => $cardTrans->getId(),
                ]);
                return $this->redirect($url);
            } else {
                $this->dbm->rollBack();
                throw $ex;
            }
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }

        try {
            $debtorTrans = $this->createAccountingRecords($cardTrans);
            $this->dbm->flushAndCommit();
        } catch (\Exception $ex) {
            $this->dbm->rollBack();
            throw $ex;
        }

        $this->logNotice("Created $debtorTrans.");
        return $this->redirectToDebtorTrans($debtorTrans);
    }

    private function redirectToDebtorTrans(DebtorTransaction $debtorTrans)
    {
        return $this->redirectToRoute('debtor_transaction_view', [
            'trans' => $debtorTrans,
        ]);
    }

    /**
     * Refunds the card transaction by issuing a new credit transaction.
     *
     * @Route("/Accounting/CardTransaction/{id}/creditRefund",
     *   name="Accounting_CardTransaction_creditRefund")
     * @Template("accounting/cardtrans/credit-refund.html.twig")
     */
    public function creditRefundAction(CardTransaction $payment, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        if (!$payment->isCaptured()) {
            throw $this->badRequest("$payment has not been captured");
        }
        if (!$payment->isSettled()) {
            throw $this->badRequest("$payment has not been settled");
        }

        $form = $this->createForm(RefundType::class, null, [
            'payment' => $payment,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $cardNumber = $data['cardNumber'];
            $amount = $data['amount'];
            $gateway = $this->getPaymentGateway();
            $this->dbm->beginTransaction();
            try {
                $credit = $gateway->credit($payment, $cardNumber, $amount);
                $debtorTrans = $this->createAccountingRecords($payment, $credit);
                $this->dbm->flushAndCommit();
                $this->logNotice("Created refund $credit.");
                $this->logNotice("Created $debtorTrans.");
                return $this->redirectToDebtorTrans($debtorTrans);
            } catch (GatewayException $ex) {
                $this->dbm->rollBack();
                $this->logError($ex->getMessage());
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }

        return [
            'cardTrans' => $payment,
            'form' => $form->createView(),
        ];
    }

    /** @return DebtorTransaction */
    private function createAccountingRecords(
        CardTransaction $payment,
        CardTransaction $credit = null)
    {
        $refunder = new CardRefund($payment);
        if ($credit) {
            $refunder->setRefund($credit);
        }
        $gateway = $this->getPaymentGateway();
        $refunder->setAccount($gateway->getDepositAccount());
        return $refunder->createRefund($this->getDataSource());
    }

    private function getDataSource()
    {
        return new RefundDataSource($this->dbm);
    }

    /** @return PaymentGateway|object */
    private function getPaymentGateway()
    {
        return $this->get(PaymentGateway::class);
    }

    /**
     * Allows the administrator to fix an error in a card transaction.
     *
     * @Route("/Accounting/CardTransaction/{id}/",
     *   name="Accounting_CardTransaction_edit")
     * @Template("accounting/cardtrans/edit.html.twig")
     */
    public function editAction(CardTransaction $trans, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $form = $this->createForm(EditType::class, $trans);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->flush();
            $this->logNotice("$trans updated successfully.");
            if (!$trans->isSettled()) {
                $this->logWarning("The changes you have made will require " .
                    "this transaction to be re-posted.");
            }
            return $this->redirectToView($trans);
        }

        return [
            'trans' => $trans,
            'form' => $form->createView(),
            'cancelUri' => $this->viewUrl($trans),
        ];
    }

    /**
     * Allows the administrator to manually enter card receipts that
     * have already been sent to the gateway but not recorded in Rialto.
     *
     * @Route("/Accounting/CardTransaction/",
     *   name="Accounting_CardTransaction_create")
     * @Template("accounting/cardtrans/create.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $template = new ManualCardReceipt($request->query->all(), $this->dbm);
        $form = $this->createForm(ManualCardReceiptType::class, $template);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $cardTrans = $template->createCardReceipt();

            $this->dbm->persist($cardTrans);
            if ($template->capture) {
                /** @var $factory DebtorTransactionFactory */
                $factory = $this->get(DebtorTransactionFactory::class);
                $debtorTrans = $factory->createCardReceipt($cardTrans);
                $this->dbm->persist($debtorTrans);
            }
            $this->dbm->flush();

            $this->logNotice("Manually entered $cardTrans successfully.");
            return $this->redirectToView($cardTrans);
        }

        return [
            'form' => $form->createView()
        ];
    }

    /**
     * Allows the administrator to record a credit card capture that has already
     * been sent to the payment gateway.
     *
     * @Route("/Accounting/CardTransaction/{id}/recordCapture",
     *   name="Accounting_CardTransaction_recordCapture")
     * @Template("accounting/cardtrans/record-capture.html.twig")
     */
    public function recordCaptureAction(CardTransaction $cardTrans, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $returnUrl = $this->viewUrl($cardTrans);

        $form = $this->createForm(ExistingCaptureType::class, null, [
            'trans' => $cardTrans,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $this->dbm->beginTransaction();
            try {
                $cardTrans->capture($data['amount'], $data['date']);
                /** @var $factory DebtorTransactionFactory */
                $factory = $this->get(DebtorTransactionFactory::class);
                $debtorTrans = $factory->createCardReceipt($cardTrans);
                $this->dbm->persist($debtorTrans);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            $this->logNotice("Recorded capture of $cardTrans.");
            return $this->redirect($returnUrl);
        }

        return [
            'cardTrans' => $cardTrans,
            'form' => $form->createView(),
            'cancel' => $returnUrl,
        ];
    }
}
