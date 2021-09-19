<?php

namespace Rialto\Accounting\Debtor;

use DateTime;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Accounting\Bank\Transaction\Orm\BankTransactionRepository;
use Rialto\Accounting\Card\CardTransaction;
use Rialto\Accounting\Card\Orm\CardTransactionRepository;
use Rialto\Accounting\Ledger\Entry\GLEntry;
use Rialto\Accounting\PaymentTransaction\PaymentTransaction;
use Rialto\Accounting\Period\Period;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Database\Orm\ErpDbManager;
use Rialto\Entity\RialtoEntity;
use Rialto\Sales\Customer\Customer;
use Rialto\Stock\Move\StockMove;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A financial transaction between the company and a customer.
 */
abstract class DebtorTransaction
implements PaymentTransaction, RialtoEntity
{
    const MONEY_PRECISION = 2;

    private $id;

    /** @var Transaction */
    private $transaction;

    /** @deprecated use $transaction instead */
    private $systemType;

    /** @deprecated use $transaction instead */
    private $systemTypeNumber;

    private $date;

    private $rate = 1;
    private $subtotalAmount;
    private $taxAmount = 0.0;

    /**
     * @deprecated "Settled" just means fully allocated, and so can be
     *   calculated.
     */
    private $settled = false;
    private $amountAllocated = 0.0;
    private $DiffOnExch;
    private $reference = '';
    private $memo = '';

    /** @var Customer */
    private $customer;
    private $period;
    private $shippingAmount = 0.0;
    private $discountAmount = 0.0;

    private $EDISent;
    private $Tpe;

    protected function __construct(Transaction $glTrans)
    {
        $this->transaction = $glTrans;
        $this->checkSystemType($glTrans->getSystemType());
        $this->systemType = $glTrans->getSystemType();
        $this->systemTypeNumber = $glTrans->getGroupNo();
        $this->date = $glTrans->getDate();
        $this->period = $glTrans->getPeriod();
        $this->memo = $glTrans->getMemo();
    }

    private function checkSystemType(SystemType $type)
    {
        if (! $this->isAllowedType($type) ) {
            throw new \InvalidArgumentException('Wrong system type');
        }
    }

    private function isAllowedType(SystemType $type)
    {
        return in_array($type->getId(), $this->getAllowedTypes());
    }

    /**
     * @return int[]
     */
    protected abstract function getAllowedTypes(): array;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Update the amount allocated and the settled flag to match the current
     * allocations.
     */
    public function updateAmountAllocated()
    {
        $this->amountAllocated = $this->calculateAmountAllocated();
        $this->settled = $this->isFullyAllocated();
    }

    /** @return float */
    private function calculateAmountAllocated()
    {
        $total = 0;
        foreach ( $this->getAllocations() as $alloc ) {
            $total += $alloc->getAmount();
        }
        $sign = $this->isInvoice() ? 1 : -1;
        return $this->round($sign * $total);
    }

    public function isSettled(): bool
    {
        return (bool) $this->isFullyAllocated();
    }

    /**
     * @return GLEntry[]
     */
    public function getGLEntries(): array
    {
        return $this->transaction->getEntries();
    }

    /**
     * @return BankTransaction[]
     */
    public function getBankTransactions(): array
    {
        /** @var $repo BankTransactionRepository*/
        $repo = ErpDbManager::getInstance()->getRepository(BankTransaction::class);;
        return $repo->findByEvent($this);
    }

    /** @return CardTransaction[] */
    public function getCardTransactions()
    {
        /** @var $repo CardTransactionRepository */
        $repo = ErpDbManager::getInstance()->getRepository(CardTransaction::class);;
        return $repo->findByEvent($this);
    }

    private function round($amount)
    {
        return round($amount, $this->getMoneyPrecision());
    }

    /** @Assert\Callback */
    public function validateAmountAllocated(ExecutionContextInterface $context)
    {
        $total = abs($this->getTotalAmount());
        $alloc = abs($this->calculateAmountAllocated());
        if ( $alloc > $total ) {
            $context->addViolation(sprintf(
                'Cannot allocate more (%s) than the total amount (%s) of %s.',
                number_format($alloc, 2),
                number_format($total, 2),
                $this
            ));
        }
    }

    public function getAmountAllocated(): float
    {
        return $this->amountAllocated;
    }

    public function getAmountUnallocated(): float
    {
        return $this->getTotalAmount() - $this->amountAllocated;
    }

    public function isFullyAllocated(): bool
    {
        return bceq($this->getAmountUnallocated(), 0, $this->getMoneyPrecision());
    }

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date ? clone $this->date : null;
    }

    /**
     * @return SystemType
     */
    public function getSystemType()
    {
        return $this->transaction->getSystemType();
    }

    /**
     * @return int
     */
    public function getSystemTypeNumber()
    {
        return $this->transaction->getGroupNo();
    }

    public function getLabel(): string
    {
        $sysType = $this->getSystemType();
        $typeNo = $this->getSystemTypeNumber();
        return strtolower($sysType->getName() .' '. $typeNo);
    }

    public function __toString()
    {
        return $this->getLabel();
    }

    public function getSummary(): string
    {
        return sprintf('%s %s for %s ($%s of %s)',
            $this->date->format('Y-m-d'),
            $this->getLabel(),
            $this->getCompanyName(),
            number_format($this->getAmountUnallocated(), 2),
            number_format($this->getTotalAmount(), 2));
    }

    private function getMoneyPrecision()
    {
        return self::MONEY_PRECISION;
    }

    public function getMemo(): string
    {
        return (string) $this->memo;
    }

    /**
     * @param string $text
     */
    public function setMemo($text): self
    {
        $this->memo = trim($text);
        return $this;
    }

    public function getReference()
    {
        return $this->reference;
    }

    /** @return Customer */
    public function getCustomer()
    {
        return $this->customer;
    }

    protected function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function getCustomerTaxId()
    {
        return $this->customer->getTaxId();
    }

    public function getDiscountAmount()
    {
        return $this->discountAmount;
    }

    /**
     * @return Period
     */
    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * @return float
     */
    public function getShippingAmount()
    {
        return $this->shippingAmount;
    }

    /** @return StockMove[] */
    public function getStockMoves()
    {
        return $this->transaction->getStockMoves();
    }

    /**
     * Returns the amount of the transaction, not including tax or shipping.
     *
     * @return float
     */
    public function getSubtotalAmount(): float
    {
        return (float) $this->subtotalAmount;
    }

    /**
     * @return float
     */
    public function getTaxAmount(): float
    {
        return $this->taxAmount;
    }

    public function setTaxAmount($taxAmount): self
    {
        $this->taxAmount = (float) $taxAmount;
        return $this;
    }

    public function getTotalAmount(): float
    {
        return $this->round(
            $this->taxAmount +
            $this->subtotalAmount +
            $this->shippingAmount +
            $this->discountAmount);
    }

    public function getTransNo()
    {
        return $this->getSystemTypeNumber();
    }

    /**
     * @param float $amount
     */
    public function setSubtotalAmount($amount): self
    {
        $this->subtotalAmount = $amount;
        return $this;
    }

    public function setReference($text): self
    {
        $this->reference = trim($text);
        return $this;
    }

    public function setShippingAmount($price): self
    {
        $this->shippingAmount = $price;
        return $this;
    }

    public function isCredit(): bool
    {
        return $this instanceof DebtorCredit;
    }

    public function isInvoice(): bool
    {
        return $this instanceof DebtorInvoice;
    }

    public function getCompanyName()
    {
        return $this->customer->getCompanyName();
    }
}
