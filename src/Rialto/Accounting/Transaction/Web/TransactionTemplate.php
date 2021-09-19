<?php

namespace Rialto\Accounting\Transaction\Web;

use DateTime;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Allows the admin to manually create Transactions.
 */
class TransactionTemplate
{
    /**
     * @var EntryTemplate[]
     * @Assert\Count(min=2, minMessage="You must enter at least two entries.")
     */
    public $entries = [];

    /**
     * @var DateTime
     * @Assert\NotNull(message="Transaction date is required.")
     * @Assert\Date
     */
    public $date;

    /**
     * @var string
     * @Assert\NotBlank(message="Transaction memo is required.")
     */
    public $memo;

    /**
     * @Assert\Callback
     */
    public function validateAmount(ExecutionContextInterface $context)
    {
        if (! bceq($this->getBalance(), 0, 2) ) {
            $context->buildViolation("Entries must sum to zero.")
                ->atPath('entries')
                ->addViolation();
        }
    }

    /**
     * @Assert\Callback
     */
    public function validateDuplicateAccounts(ExecutionContextInterface $context)
    {
        $index = [];
        foreach ( $this->entries as $entry ) {
            $id = $entry->account->getId();
            if ( isset($index[$id]) ) {
                $context->addViolation("\"{$entry->account}\" cannot appear multiple times.");
            }
            $index[$id] = true;
        }
    }

    /**
     * @return float The total of the entry amounts.
     */
    private function getBalance()
    {
        $total = 0;
        foreach ( $this->entries as $entry ) {
            $total += $entry->amount;
        }
        return $total;
    }

    /** @return Transaction */
    public function createTransaction(SystemType $sysType)
    {
        $trans = new Transaction($sysType);
        $trans->setDate($this->date);
        $trans->setMemo($this->memo);
        foreach ( $this->entries as $entry ) {
            $trans->addEntry($entry->account, $entry->amount);
        }
        return $trans;
    }
}
