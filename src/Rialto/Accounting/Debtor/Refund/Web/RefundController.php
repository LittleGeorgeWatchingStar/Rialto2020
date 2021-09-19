<?php

namespace Rialto\Accounting\Debtor\Refund\Web;

use Rialto\Accounting\Bank\Account\UsedChequeNumberException;
use Rialto\Accounting\Debtor\Refund\BankRefund;
use Rialto\Accounting\Debtor\Refund\RefundDataSource;
use Rialto\Sales\Customer\Customer;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class RefundController extends RialtoController
{
    /**
     * @Route("/Debtor/Refund/{id}/bankRefund",
     *   name="Debtor_Refund_bankRefund")
     * @Template("accounting/debtor/refund/bankRefund.html.twig")
     */
    public function bankRefundAction(Customer $customer, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $refund = new BankRefund($customer);

        $form = $this->createForm(BankRefundType::class, $refund, [
            'customer' => $customer,
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $debtorTrans = $refund->createRefund($this->getDataSource());
                $this->dbm->flushAndCommit();
                $this->logNotice(sprintf(
                    'Refunded %s for $%s.',
                    $customer->getName(),
                    number_format($refund->getAmount(), 2)
                ));
                return $this->redirectToRoute('debtor_transaction_view', [
                    'trans' => $debtorTrans->getId(),
                ]);
            } catch (UsedChequeNumberException $ex) {
                $this->dbm->rollBack();
                $this->logException($ex);
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }

        return [
            'customer' => $customer,
            'form' => $form->createView(),
        ];
    }

    private function getDataSource()
    {
        return new RefundDataSource($this->dbm);
    }
}
