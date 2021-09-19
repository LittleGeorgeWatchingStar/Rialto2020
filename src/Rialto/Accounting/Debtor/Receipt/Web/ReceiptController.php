<?php

namespace Rialto\Accounting\Debtor\Receipt\Web;

use Rialto\Accounting\Debtor\Credit\WireReceipt;
use Rialto\Accounting\Debtor\DebtorTransactionFactory;
use Rialto\Sales\Customer\Customer;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for manually entering the receipt of customer payments.
 *
 * @Route("/Debtor/Receipt")
 */
class ReceiptController extends RialtoController
{
    /**
     * Manual customer bank receipt (eg, wire transfer, cheque).
     *
     * @Route("/{id}/bank", name="Debtor_Receipt_bank")
     * @Template("accounting/debtor/receipt/bank.html.twig")
     */
    public function bankAction(Customer $customer, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $wireReceipt = new WireReceipt($customer);
        $form = $this->createForm(BankReceiptType::class, $wireReceipt, [
            'customer' => $customer
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                /** @var $factory DebtorTransactionFactory */
                $factory = $this->get(DebtorTransactionFactory::class);
                $debtorTrans = $factory->createCredit($wireReceipt);
                $this->dbm->persist($debtorTrans);
                $this->dbm->flushAndCommit();
                $this->logNotice(ucfirst("$debtorTrans entered successfully."));
                return $this->redirectToRoute('debtor_transaction_view', [
                    'trans' => $debtorTrans->getId(),
                ]);
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }

        return [
            'customer' => $customer,
            'form' => $form->createView(),
            'cancelUri' => $this->generateUrl('customer_view', [
                'customer' => $customer->getId(),
            ]),
        ];
    }

}
