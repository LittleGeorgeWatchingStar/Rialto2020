<?php

namespace Rialto\Accounting\Bank\Statement\Match;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Rialto\Accounting\Bank\Account\Repository\BankAccountRepository;
use Rialto\Accounting\Bank\Statement\BankStatement;
use Rialto\Accounting\Bank\Statement\BankStatementPattern;
use Rialto\Accounting\Bank\Statement\Orm\BankStatementRepository;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Company\Company;
use Rialto\Database\Orm\DbManager;

/**
 * Implements how a bank statement line should be matched off against
 * an accounting transaction. Subclasses implement specific rules for doing
 * this; for example:
 *  - adjusting the transaction amount to match the bank statement;
 *  - creating transactions that have not been entered yet;
 *  - matching against various types of accounting transactions.
 */
abstract class MatchStrategy
{
    /** @var DbManager */
    protected $dbm;

    /** @var Company */
    protected $company;

    /** @var BankStatementPattern */
    protected $pattern;

    private $originalStatement;

    /**
     * A list of all accepted bank statements, including the original one.
     * @var BankStatement[]|Collection
     */
    protected $bankStatements;

    /**
     * A list of additional bank statements to be matched along with
     * the original one.
     * @var BankStatement[]|Collection
     */
    private $additionalStatements;

    /** @var BankAccountRepository */
    protected $bankAccountRepository;

    /** @var BankTransaction[]|Collection */
    protected $bankTransactions;

    public function __construct(BankStatement $statement,
                                DbManager $dbm,
                                BankAccountRepository $bankAccountRepository)
    {
        $this->dbm = $dbm;
        $this->originalStatement = $statement;
        $this->initializeCollections();
        $this->bankStatements[] = $statement;
        $this->bankAccountRepository = $bankAccountRepository;
    }

    protected function initializeCollections()
    {
        $this->bankStatements = new ArrayCollection();
        $this->additionalStatements = new ArrayCollection();
        $this->bankTransactions = new ArrayCollection();
    }

    public function setCompany(Company $company)
    {
        $this->company = $company;
    }

    public function setPattern(BankStatementPattern $pattern)
    {
        $this->pattern = $pattern;
        $this->loadAdditionalStatements();
    }

    private function loadAdditionalStatements()
    {
        if (! $this->pattern->hasAdditionalStatements() ) return;

        /** @var $repo BankStatementRepository */
        $repo = $this->dbm->getRepository(BankStatement::class);
        $this->additionalStatements = $repo->findAdditionalStatements($this, $this->pattern);
    }

    public function getType()
    {
        return $this->pattern ? $this->pattern->getStrategy() : null;
    }

    /** @todo
     *  @return BankStatement
     */
    public function getStatement()
    {
        return $this->originalStatement;
    }

    public function getAcceptedStatements()
    {
        return new ArrayCollection($this->bankStatements->toArray());
    }

    public function addAcceptedStatement(BankStatement $statement)
    {
        $this->bankStatements[] = $statement;
    }

    public function removeAcceptedStatement(BankStatement $statement)
    {
        /* The original statement cannot be removed */
        if ( $statement === $this->originalStatement ) return;
        $this->bankStatements->removeElement($statement);
    }

    public function hasAdditionalStatements()
    {
        return count($this->additionalStatements) > 0;
    }

    public function getAdditionalStatements()
    {
        return $this->additionalStatements;
    }

    public function getTotalOutstanding()
    {
        $total = 0;
        foreach ( $this->bankStatements as $st ) {
            $total += $st->getAmountOutstanding();
        }
        return $total;
    }

    /** @return \DateTime */
    public function getDate()
    {
        $st = $this->getStatement();
        return $st->getDate();
    }

    public abstract function loadMatchingRecords();

    public abstract function hasMatchingRecords(): bool;

    public abstract function getMatchingBankTransactions();

    public abstract function save();

    protected function linkBankTransactions()
    {
        foreach ( $this->bankTransactions as $bankTrans ) {
            $this->linkBankTransaction($bankTrans);
        }
    }

    private function linkBankTransaction(BankTransaction $trans)
    {
        foreach ( $this->bankStatements as $statement ) {
            if ( $this->round($trans->getAmountOutstanding()) == 0 ) return;
            if ( $this->round($statement->getAmountOutstanding()) == 0 ) continue;

            $this->linkTransactionToStatement($trans, $statement);
        }
    }

    /**
     * @param BankTransaction $trans
     * @param BankStatement $statement
     * @return double
     *  The amount cleared.
     */
    private function linkTransactionToStatement(BankTransaction $trans, BankStatement $statement)
    {
        $match = $statement->addBankTransaction($trans);
        return $match->getAmountCleared();
    }

    protected function round($amount)
    {
        return round($amount, BankStatement::MONEY_PRECISION);
    }
}
