<?php

namespace Rialto\Accounting\Bank\Transaction;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Accounting\AccountingEvent;
use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Accounting\Bank\Statement\BankStatementMatch;
use Rialto\Accounting\Bank\Transfer\BankTransfer;
use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Ledger\Entry\GLEntry;
use Rialto\Accounting\Period\Orm\PeriodRepository;
use Rialto\Accounting\Period\Period;
use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Database\Orm\ErpDbManager;
use Rialto\Entity\RialtoEntity;
use Rialto\IllegalStateException;

class BankTransaction implements AccountingEvent, RialtoEntity
{
    const MONEY_PRECISION = 2;
    const TYPE_CHEQUE = 'Cheque';
    const TYPE_DIRECT = 'Direct Credit';

    private $id;

    /** @var Transaction */
    private $transaction;

    /** @deprecated use $transaction instead */
    private $systemType;
    /** @deprecated use $transaction instead */
    private $systemTypeNumber;

    private $reference = '';
    private $ExRate = 1;
    private $date;
    private $bankTransType = '';
    private $amount;
    private $CurrCode = Currency::USD;
    private $printed = false;
    private $chequeNumber = 0;

    /** @deprecated */
    private $amountCleared = 0;

    /** @var BankStatementMatch[] */
    private $matches;

    /** @var BankAccount */
    private $bankAccount;

    public function __construct(
        Transaction $glTrans,
        BankAccount $account,
        $bankTransType = '')
    {
        $this->transaction = $glTrans;
        $this->systemType = $glTrans->getSystemType();
        $this->systemTypeNumber = $glTrans->getSystemTypeNumber();
        $this->bankAccount = $account;
        $this->bankTransType = $bankTransType;
        $this->setDate($glTrans->getDate());
        $this->setReference($glTrans->getMemo());
        $this->matches = new ArrayCollection();
    }

    public function __toString()
    {
        return 'bank transaction ' . $this->id;
    }

    /** @return string[] */
    public static function getValidPaymentTypes()
    {
        return [
            self::TYPE_CHEQUE => self::TYPE_CHEQUE,
            self::TYPE_DIRECT => self::TYPE_DIRECT,
        ];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSystemType()
    {
        return $this->transaction->getSystemType();
    }

    public function getSystemTypeNumber()
    {
        return $this->transaction->getGroupNo();
    }

    public function isType($sysType): bool
    {
        return $this->getSystemType()->isType($sysType);
    }

    public function isCreditCardSweep(): bool
    {
        return $this->isType(SystemType::CREDIT_CARD_SWEEP);
    }

    public function setChequeNumber($chequeNo)
    {
        if (!$this->isCheque()) {
            throw new IllegalStateException(
                'Cannot set cheque number on non-cheque bank transaction'
            );
        }
        $this->chequeNumber = (int) $chequeNo;
    }

    public function getChequeNumber()
    {
        return $this->chequeNumber;
    }

    public function getBankAccount(): BankAccount
    {
        return $this->bankAccount;
    }

    public function getBankGLAccount(): GLAccount
    {
        return $this->bankAccount->getGLAccount();
    }

    public function setDate(DateTime $date)
    {
        $this->date = clone $date;
    }

    /** @return DateTime */
    public function getDate()
    {
        return clone $this->date;
    }

    public function getPeriod()
    {
        $dbm = ErpDbManager::getInstance();
        /** @var $repo PeriodRepository */
        $repo = $dbm->getRepository(Period::class);
        return $repo->findForDate($this->getDate());
    }

    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getAmountCleared()
    {
        $total = 0;
        foreach ($this->getMatches() as $match) {
            $total += $match->getAmountCleared();
        }
        return $total;
    }

    /** @return BankStatementMatch[] */
    public function getMatches()
    {
        return $this->matches->toArray();
    }

    public function addMatch(BankStatementMatch $match)
    {
        $this->matches[] = $match;
    }

    public function getBankStatements()
    {
        return array_map(function (BankStatementMatch $match) {
            return $match->getBankStatement();
        }, $this->getMatches());
    }

    /** @return GLEntry[] */
    public function getGLEntries()
    {
        $dbm = ErpDbManager::getInstance();
        $mapper = $dbm->getRepository(GLEntry::class);
        return $mapper->findByEvent($this);
    }

    /** @return DebtorTransaction[] */
    public function getDebtorTransactions()
    {
        $dbm = ErpDbManager::getInstance();
        $mapper = $dbm->getRepository(DebtorTransaction::class);
        return $mapper->findByEvent($this);
    }

    /**
     * @return DebtorTransaction
     * @throws \UnexpectedValueException if this bank trans does not have
     *  exactly one debtor transaction.
     */
    public function getDebtorTransaction()
    {
        $transactions = $this->getDebtorTransactions();
        $count = count($transactions);
        if ($count != 1) {
            throw new \UnexpectedValueException("$this has $count debtor transactions");
        }
        return reset($transactions);
    }

    /** @return SupplierTransaction[] */
    public function getSupplierTransactions()
    {
        $dbm = ErpDbManager::getInstance();
        $mapper = $dbm->getRepository(SupplierTransaction::class);
        return $mapper->findByEvent($this);
    }

    /**
     * @throws \UnexpectedValueException if this bank trans does not have
     *  exactly one supplier transaction.
     */
    public function getSupplierTransaction(): SupplierTransaction
    {
        $transactions = $this->getSupplierTransactions();
        $count = count($transactions);
        if ($count != 1) {
            throw new \UnexpectedValueException("$this has $count supplier transactions");
        }
        return reset($transactions);
    }

    public function getBankTransfer(): ?BankTransfer
    {
        $dbm = ErpDbManager::getInstance();
        $mapper = $dbm->getRepository(BankTransfer::class);
        return $mapper->findOneBy([
            'fromTransaction' => $this,
        ]) ?? $mapper->findOneBy([
            'toTransaction' => $this,
                ]);
    }

    /** @deprecated */
    public function refreshAmountCleared()
    {
        $this->amountCleared = $this->getAmountCleared();
    }

    public function getAmountOutstanding()
    {
        return $this->amount - $this->getAmountCleared();
    }

    public function setReference($ref)
    {
        $this->reference = trim($ref);
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getMemo(): string
    {
        return $this->getReference();
    }

    public function isPrinted(): bool
    {
        return $this->printed;
    }

    public function setPrinted()
    {
        $this->printed = true;
    }

    public function isCheque(): bool
    {
        return self::TYPE_CHEQUE == $this->bankTransType;
    }

    public function isCleared(): bool
    {
        return $this->getAmountCleared() > 0;
    }

    public function isFullyCleared(): bool
    {
        return $this->round($this->amount - $this->getAmountCleared()) == 0;
    }

    private function round($amount): float
    {
        return round($amount, self::MONEY_PRECISION);
    }

    public function isOutstanding(): bool
    {
        if ($this->amount == 0) return false;
        if ($this->isCleared()) return false;
        return true;
    }

    public function cancel()
    {
        if ($this->isCleared()) {
            throw new IllegalStateException("Cannot cancel a cleared cheque.");
        }
        $this->amount = 0;
        $this->printed = true;
    }
}
