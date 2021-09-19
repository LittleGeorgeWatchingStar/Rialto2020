<?php

namespace Rialto\Payment\Sweep;

use DateTime;
use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Account\Repository\BankAccountRepository;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Card\CardTransaction;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Database\Orm\DbManager;
use Rialto\Payment\PaymentGateway;
use Rialto\Payment\PaymentMethod\PaymentMethodGroup;
use Rialto\Util\Collection\IndexBuilder;
use SplObjectStorage as Map;

/**
 * Sweeps all card transactions posted in a day into
 * bank transactions.
 */
class CardTransactionSweep
{
    /** @var DbManager */
    private $dbm;

    /** @var SystemType */
    private $sysType;

    /** @var GLAccount */
    private $fromAccount;

    /** @var BankAccount */
    private $toAccount;

    public function __construct(DbManager $dbm,
                                PaymentGateway $gateway,
                                BankAccountRepository $bankAccountRepository)
    {
        $this->dbm = $dbm;
        $this->sysType = SystemType::fetch(SystemType::CREDIT_CARD_SWEEP, $dbm);
        $this->fromAccount = $gateway->getDepositAccount();
        $this->toAccount = $bankAccountRepository->getDefaultChecking();
    }

    /**
     * @param CardTransaction[] $transactions
     * @return BankTransaction[]
     */
    public function sweep($transactions)
    {
        $index = $this->indexTransactionsByDateAndGroup($transactions);
        $bankTransList = [];
        foreach ($index as $postDate) {
            $byGroup = $index[$postDate];
            foreach ($byGroup as $group) {
                /** @var $group PaymentMethodGroup */
                $list = $byGroup[$group];
                $glTrans = $this->createCoreTransaction($postDate, $group);
                $total = $this->getTotalAmount($list);
                $bankTransList[] = $this->createSweepTransaction($glTrans, $total);
                if ($group->isSweepFeesDaily()) {
                    $bankTransList[] = $this->createFeeTransaction($glTrans, $group, $total);
                }

                $this->dbm->persist($glTrans);
            }
        }
        $this->markAsSettled($transactions);
        return array_filter($bankTransList); // remove null values
    }

    /**
     * @param CardTransaction[] $transactions
     * @return Map<DateTime, Map<PaymentMethodGroup, CardTransaction[]>>
     */
    private function indexTransactionsByDateAndGroup($transactions)
    {
        $index = new Map();
        $byDate = IndexBuilder::fromObjects($transactions, 'getPostDate');

        foreach ($byDate as $date) {
            $list = $byDate[$date];
            $byGroup = IndexBuilder::fromObjects($list, 'getPaymentMethodGroup');
            $index[$date] = $byGroup;
        }
        return $index;
    }

    /** @return Transaction */
    private function createCoreTransaction(DateTime $postDate, PaymentMethodGroup $group)
    {
        $glTrans = new Transaction($this->sysType);
        $glTrans->setDate($postDate);
        $glTrans->setMemo(sprintf('Sweep %s - %s',
            $group,
            $postDate->format('Y-m-d')));
        return $glTrans;
    }

    /**
     * @param CardTransaction[] $list
     * @return float
     */
    private function getTotalAmount($list)
    {
        $total = 0.0;
        foreach ($list as $cardTrans) {
            assertion($cardTrans->isCaptured(), "$cardTrans is not captured");
            $total += $cardTrans->getAmountCaptured();
        }
        return $total;
    }

    /** @return BankTransaction */
    private function createSweepTransaction(Transaction $glTrans, $totalAmount)
    {
        $glTrans->addEntry($this->fromAccount, -$totalAmount);
        $glTrans->addEntry($this->toAccount->getGLAccount(), $totalAmount);

        $bankTrans = new BankTransaction($glTrans, $this->toAccount);
        $bankTrans->setAmount($totalAmount);
        $this->dbm->persist($bankTrans);
        return $bankTrans;
    }

    private function createFeeTransaction(
        Transaction $glTrans,
        PaymentMethodGroup $group,
        $totalAmount)
    {
        $feeAccount = $group->getFeeAccount();
        $feeAmount = $group->getTotalFees($totalAmount);
        $memo = 'FEES: ' . $glTrans->getMemo();
        $glTrans->addEntry($this->toAccount->getGLAccount(), -$feeAmount, $memo);
        $glTrans->addEntry($feeAccount, $feeAmount, $memo);

        $bankTrans = new BankTransaction($glTrans, $this->toAccount);
        $bankTrans->setAmount(-$feeAmount);
        $bankTrans->setReference($memo);
        $this->dbm->persist($bankTrans);
        return $bankTrans;
    }

    /**
     * @param CardTransaction[] $transactions
     */
    private function markAsSettled($transactions)
    {
        foreach ($transactions as $cardTrans) {
            assertion(! $cardTrans->isSettled(), "$cardTrans is already settled");
            $cardTrans->setSettled(true);
        }
    }
}
