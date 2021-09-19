<?php

namespace Rialto\Accounting\Bank\Statement;

use Rialto\Accounting\Bank\Account\Repository\BankAccountRepository;
use Rialto\Accounting\Bank\Statement\Match\MatchStrategy;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Database\Orm\DbManager;
use Rialto\Entity\RialtoEntity;
use Rialto\Purchasing\Supplier\Supplier;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A pattern for matching accounting records to a bank statement line.
 *
 * There are different strategies for reconciling a line item on a bank statement
 * with a corresponding accounting transaction (@see MatchStrategy).
 * A BankStatementPattern contains a pattern to match against the statement
 * item (@see matchesStatement(), getStatementPattern()), a reconciliation
 * strategy to use if the statement item matches (@see createStrategy()), and
 * additional information that the reconciliation strategy needs in order to
 * do its job.
 */
class BankStatementPattern implements RialtoEntity
{
    private $id;

    /**
     * @var string
     */
    private $strategy;

    /**
     * @Assert\NotBlank(message="Statement pattern cannot be blank.")
     */
    private $statementPattern;

    private $additionalStatementPattern = '';

    /**
     * @Assert\Type(type="numeric", message="Additional statement date constraint must be numeric.")
     */
    private $additionalStatementDateConstraint = null;
    private $referencePattern = '';
    private $dateConstraint = 5;
    private $amountConstraint = 0.01;
    private $updatePattern = '';

    /** @Assert\Type(type="numeric", message="Sort order must be numeric.") */
    private $sortOrder = 0;

    /** @var Supplier|null  */
    private $supplier = null;
    private $adjustmentAccount;

    public static function create($strategy)
    {
        $pattern = new self();
        $pattern->strategy = $strategy;
        return $pattern;
    }

    public function getId()
    {
        return $this->id;
    }

    public function matchesStatement(BankStatement $statement)
    {
        $pattern = $this->sqlPatternToRegex($this->statementPattern);
        return (bool) preg_match($pattern, $statement->getDescription());
    }

    private function sqlPatternToRegex($pattern)
    {
        $pattern = str_replace('%', '.*', $pattern);
        $pattern = str_replace('/', '\/', $pattern);
        return "/$pattern/";
    }

    public function getStatementPattern()
    {
        return $this->statementPattern;
    }

    public function setStatementPattern($pattern)
    {
        $this->statementPattern = trim($pattern);
    }

    private function prepPattern($pattern)
    {
        $pattern = str_replace('/', '\/', $pattern);
        return "/$pattern/";
    }

    /** @return MatchStrategy  */
    public function createStrategy(BankStatement $statement,
                                   DbManager $dbm,
                                   BankAccountRepository $bankAccountRepo)
    {
        $strategy = $this->instantiateStrategy($statement, $dbm, $bankAccountRepo);
        $strategy->setPattern($this);
        $strategy->loadMatchingRecords();
        return $strategy;
    }

    /**
     * @return MatchStrategy
     */
    private function instantiateStrategy(BankStatement $statement,
                                         DbManager $dbm,
                                         BankAccountRepository $bankAccountRepo)
    {
        $className = sprintf(
            '\Rialto\Accounting\Bank\Statement\Match\%sStrategy',
            $this->strategy
        );
        return new $className($statement, $dbm, $bankAccountRepo);
    }

    public function getStrategy()
    {
        return $this->strategy;
    }

    public function hasAdditionalStatements()
    {
        return (bool) $this->additionalStatementPattern;
    }

    public function getAdditionalStatementPattern()
    {
        return $this->additionalStatementPattern;
    }

    public function setAdditionalStatementPattern($pattern)
    {
        $this->additionalStatementPattern = trim($pattern);
    }

    public function hasAdditionalStatementDateConstraint()
    {
        return is_numeric($this->additionalStatementDateConstraint);
    }

    public function getAdditionalStatementDateConstraint()
    {
        return $this->additionalStatementDateConstraint;
    }

    public function setAdditionalStatementDateConstraint($dateConstraint)
    {
        $this->additionalStatementDateConstraint = $dateConstraint;
    }

    public function getReferencePattern()
    {
        return $this->referencePattern;
    }

    public function setReferencePattern($pattern)
    {
        $this->referencePattern = trim($pattern);
    }

    /**
     * @Assert\Callback
     */
    public function validateReferencePattern(ExecutionContextInterface $context)
    {
        if ('ExistingSupplierInvoice' === $this->strategy && !$this->referencePattern) {
            $context->buildViolation('Reference pattern is required.')
                ->atPath('referencePattern')
                ->addViolation();
        }
    }

    public function hasDateConstraint()
    {
        return is_numeric($this->dateConstraint);
    }

    public function getDateConstraint()
    {
        return $this->dateConstraint;
    }

    public function setDateConstraint($dateConstrain)
    {
        $this->dateConstraint = $dateConstrain;
    }

    public function hasAmountConstraint()
    {
        return is_numeric($this->amountConstraint);
    }

    public function getAmountConstraint()
    {
        return $this->amountConstraint;
    }

    public function setAmountConstraint($constraint)
    {
        $this->amountConstraint = $constraint;
    }

    /** @return Supplier|null */
    public function getSupplier()
    {
        return $this->supplier;
    }

    public function setSupplier(Supplier $supplier = null)
    {
        $this->supplier = $supplier;
    }

    public function getAdjustmentAccount()
    {
        return $this->adjustmentAccount;
    }

    public function setAdjustmentAccount(GLAccount $account = null)
    {
        $this->adjustmentAccount = $account;
    }

    public function getUpdatePattern()
    {
        return $this->updatePattern;
    }

    public function setUpdatePattern($pattern)
    {
        $this->updatePattern = trim($pattern);
    }

    public function matchesUpdatePattern($string)
    {
        if (! $this->updatePattern ) return false;
        $pattern = $this->prepPattern($this->updatePattern);
        return preg_match($pattern, $string);
    }

    public function getSortOrder()
    {
        return (int) $this->sortOrder;
    }

    public function setSortOrder($orderBy)
    {
        $this->sortOrder = $orderBy;
    }

    public function isNew()
    {
        return ! $this->getId();
    }
}
