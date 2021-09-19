<?php

namespace Rialto\Accounting\PaymentTransaction\Web;

use Rialto\Accounting\Debtor\DebtorAllocation;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Accounting\PaymentTransaction\PaymentAllocation;
use Rialto\Accounting\PaymentTransaction\PaymentTransaction;
use Rialto\Accounting\Supplier\SupplierAllocation;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * For managing the allocations between PaymentTransaction records.
 */
class PaymentAllocationController extends RialtoController
{
    /**
     * @Route("/record/Accounting/DebtorAllocation/{id}",
     *   name="Accounting_CustomerAllocation_delete")
     * @Method("DELETE")
     */
    public function deleteCustomerAllocationAction(DebtorAllocation $alloc)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        return $this->deleteHelper($alloc);
    }

    /**
     * @Route("/record/Accounting/SupplierAllocation/{id}",
     *   name="Accounting_SupplierAllocation_delete")
     * @Method("DELETE")
     */
    public function deleteSupplierAllocationAction(SupplierAllocation $alloc)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        return $this->deleteHelper($alloc);
    }

    private function deleteHelper(PaymentAllocation $alloc)
    {
        $invoice = $alloc->getInvoice();
        $credit = $alloc->getCredit();
        $id = $alloc->getId();

        $this->dbm->beginTransaction();
        try {
            $invoice->removeAllocation($alloc);
            $this->dbm->flushAndCommit();
        }
        catch ( \Exception $ex ) {
            $this->dbm->rollBack();
            throw $ex;
        }

        $this->logNotice("Deleted allocation $id between $invoice and $credit.");
        $returnUri = $this->getReturnUri($this->viewUrl($invoice));
        return $this->redirect($returnUri);
    }

    private function viewUrl(PaymentTransaction $trans)
    {
        if ($trans instanceof DebtorTransaction) {
            return $this->generateUrl('debtor_transaction_view', [
                'trans' => $trans->getId(),
            ]);
        } else {
            return $this->generateUrl('supplier_transaction_view', [
                'trans' => $trans->getId(),
            ]);
        }
    }
}
