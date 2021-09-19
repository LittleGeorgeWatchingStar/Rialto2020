<?php

namespace Rialto\Accounting\Bank\Statement\Web;

use Rialto\Accounting\Bank\Statement\BankStatementMatch;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * Controller for managing the reconciliation records between bank statements
 * and bank transactions.
 */
class BankStatementMatchController extends RialtoController
{
    /**
     * @Route("/record/Accounting/BankStatementMatch/{id}",
     *   name="Accounting_BankStatementMatch_delete")
     * @Method("DELETE")
     */
    public function deleteAction(BankStatementMatch $match)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $transaction = $match->getBankTransaction();
        $returnUri = $this->getReturnUri($this->bankTransUrl($transaction));
        $id = $match->getId();

        $this->dbm->remove($match);
        $this->dbm->flush();

        $this->logNotice("Bank statement match $id deleted successfully.");
        return $this->redirect($returnUri);
    }

    private function bankTransUrl(BankTransaction $transaction)
    {
        return $this->generateUrl('bank_transaction_view', [
            'id' => $transaction->getId(),
        ]);
    }

}
