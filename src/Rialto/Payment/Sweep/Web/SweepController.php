<?php

namespace Rialto\Payment\Sweep\Web;

use DateTime;
use Exception;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Card\CardTransaction;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Payment\Sweep\CardTransactionSweep;
use Rialto\Security\Role\Role;
use Rialto\Util\Collection\IndexBuilder;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Sweeps unposted credit card transactions into one bank transaction
 * per day.
 */
class SweepController extends RialtoController
{
    /**
     * @Route("/Payment/CardTransaction/sweep/{date}/", name="Payment_CardTransaction_sweep")
     * @Template("payment/sweep/sweep.html.twig")
     */
    public function sweepAction(DateTime $date, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $transactions = $this->dbm->getRepository(CardTransaction::class)
            ->findSweepable($date);

        $sweeps = $this->dbm->getRepository(BankTransaction::class)
            ->findByFilters([
                'systemType' => SystemType::CREDIT_CARD_SWEEP,
                'date' => $date->format('Y-m-d'),
            ]);

        if ( $request->isMethod('POST') ) {
            $this->dbm->beginTransaction();
            try {
                $this->markTransactionsUnsettled($transactions);
                $this->deleteBankTransactions($sweeps);
                $this->sweep($transactions);
                $this->dbm->flushAndCommit();

            } catch ( Exception $ex ) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $this->logNotice("Card transactions re-swept successfully.");
            return $this->redirect($this->getCurrentUri());
        }

        $index = IndexBuilder::fromObjects($transactions, 'getPaymentMethodGroup');
        return [
            'date' => $date,
            'index' => $index,
            'sweeps' => $sweeps,
        ];
    }

    /**
     * @param CardTransaction[] $transactions
     */
    private function markTransactionsUnsettled($transactions)
    {
        foreach ( $transactions as $cardTrans ) {
            $cardTrans->setSettled(false);
        }
    }

    /**
     * @param BankTransaction[] $sweeps
     */
    private function deleteBankTransactions($sweeps)
    {
        foreach ( $sweeps as $bankTrans ) {
            foreach ( $bankTrans->getGLEntries() as $entry ) {
                $this->dbm->remove($entry);
            }
            $this->dbm->remove($bankTrans);
        }
    }

    private function sweep($transactions)
    {
        /* @var $sweeper CardTransactionSweep */
        $sweeper = $this->get(CardTransactionSweep::class);
        $sweeper->sweep($transactions);
    }
}
