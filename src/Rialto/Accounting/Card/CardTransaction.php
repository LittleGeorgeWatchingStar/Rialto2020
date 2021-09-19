<?php

namespace Rialto\Accounting\Card;

use DateTime;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Accounting\Debtor\Orm\DebtorTransactionRepository;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Database\Orm\ErpDbManager;
use Rialto\Entity\RialtoEntity;
use Rialto\Payment\PaymentMethod\PaymentMethod;
use Rialto\Payment\PaymentMethod\PaymentMethodGroup;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Order\SalesOrder;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A credit card transaction, such as an approval or charge.
 */
class CardTransaction implements RialtoEntity
{
    /** Card transactions captured after 3pm are posted the following day. */
    const POST_CUTOFF_HOUR = 15;

    private $id;

    /** @var Transaction|null */
    private $accountingTransaction = null;

    /**
     * @var SystemType
     * @Assert\NotNull
     */
    private $systemType;

    /** @Assert\NotBlank */
    private $systemTypeNumber;

    /**
     * @Assert\NotBlank
     * @Assert\Type(type="numeric", message="Transaction ID should be an integer.")
     * @Assert\Range(min=1, minMessage="Transaction ID must be at least {{ limit }}.")
     */
    private $transactionId = 0;

    /**
     * @Assert\NotBlank
     * @Assert\Length(max="20")
     */
    private $authCode;
    private $amountAuthorized;

    /** @var DateTime */
    private $dateCreated;

    private $amountCaptured = 0.0;

    /** @var DateTime */
    private $dateCaptured = null;

    /**
     * The date on which this transaction should be posted/swept.
     * @var DateTime
     */
    private $postDate = null;

    private $approved = false;
    private $void = false;
    private $settled = false;

    /** @var PaymentMethod */
    private $creditCard;

    /** @var CardTransaction */
    private $referenceTransaction = null;

    /** @var Customer */
    private $customer = null;

    /** @var SalesOrder */
    private $salesOrder = null;

    public function __construct(
        PaymentMethod $card,
        $transactionId,
        $authCode,
        $amountAuthorized,
        DateTime $created = null)
    {
        $this->creditCard = $card;
        $this->transactionId = $transactionId;
        $this->authCode = trim($authCode);
        $this->amountAuthorized = $amountAuthorized;
        $this->dateCreated = $created ? clone $created : new DateTime();
    }

    /**
     * Factory method that creates a pre-captured card transaction.
     *
     * Useful for refunds, manually-entered receipts, etc.
     *
     * @return CardTransaction
     */
    public static function captured(
        PaymentMethod $card,
        $transactionId,
        $authCode,
        $amount,
        DateTime $created = null)
    {
        $ct = new self($card, $transactionId, $authCode, $amount, $created);
        $ct->capture($amount, $created);
        return $ct;
    }

    /**
     * Factory method that creates a refund for $payment.
     *
     * @return CardTransaction
     */
    public static function refund(
        self $payment,
        PaymentMethod $card,
        $transactionId,
        $authCode,
        $amount,
        DateTime $created = null)
    {
        assertion($amount < 0, 'card refund amounts must be negative');
        assertion(abs($amount) <= $payment->getAmountCaptured(),
            'cannot refund more than original amount');
        $refund = self::captured($card, $transactionId, $authCode, $amount, $created);
        $refund->setReferenceTransaction($payment);
        $order = $payment->getSalesOrder();
        if ( $order ) {
            $order->addCardTransaction($refund);
        } else {
            $refund->setCustomer($payment->getCustomer());
        }
        return $refund;
    }

    /**
     * @return float
     */
    public function getAmountAuthorized()
    {
        return (float) $this->amountAuthorized;
    }

    /**
     * @return string
     */
    public function getAuthCode()
    {
        return $this->authCode;
    }

    /**
     * @return PaymentMethod
     */
    public function getCreditCard()
    {
        return $this->creditCard;
    }

    /**
     * Used by admins to fix an incorrect card transaction.
     */
    public function setCreditCard(PaymentMethod $card)
    {
        if ( $card->getGroup() !== $this->getPaymentMethodGroup() ) {
            $this->settled = false;
        }
        $this->creditCard = $card;
    }

    /** @return PaymentMethodGroup */
    public function getPaymentMethodGroup()
    {
        return $this->creditCard->getGroup();
    }

    /** @return string */
    public function getCardName()
    {
        return $this->getCreditCard()->getName();
    }

    public function isCaptured()
    {
        return null != $this->dateCaptured;
    }

    public function canBeCaptured()
    {
        $tooLate = $this->isCaptured()
            || $this->isVoid()
            || $this->isSettled();
        return ! $tooLate;
    }

    /**
     * Captures this card authorization.
     * @return CardTransaction
     */
    public function capture($amount, DateTime $date = null)
    {
        assertion($this->canBeCaptured(), "$this cannot be captured");
        assertion(abs($amount) <= abs($this->amountAuthorized),
            "cannot capture more than authorized");
        assertion($amount != 0, "cannot capture zero");

        $this->amountCaptured = $amount;
        $this->dateCaptured = $date ? clone $date : new DateTime();
        $this->determineDateToPost();

        return $this;
    }

    private function determineDateToPost()
    {
        $postDate = clone $this->dateCaptured;
        $hour = (int) $postDate->format('H');
        /* Transactions captured after the cutoff time are posted the
         * following day. */
        if ($hour >= self::POST_CUTOFF_HOUR) {
            $postDate->modify('+1 day');
        }
        $this->postDate = $postDate;
        assertion($this->postDate !== $this->dateCaptured);
    }

    /**
     * @return float
     */
    public function getAmountCaptured()
    {
        return $this->amountCaptured;
    }

    /**
     * @return DateTime
     */
    public function getDateCaptured()
    {
        return $this->dateCaptured ? clone $this->dateCaptured : null;
    }

    /**
     * @return DebtorTransaction|null
     */
    public function getDebtorTransaction()
    {
        $sysType = $this->getSystemType();
        if (! $sysType ) return null;
        $dbm = ErpDbManager::getInstance();
        /** @var $repo DebtorTransactionRepository */
        $repo = $dbm->getRepository(DebtorTransaction::class);
        return $repo->findOneByType($sysType, $this->systemTypeNumber);
    }

    /** @return Customer|null */
    public function getCustomer()
    {
        return $this->customer;
    }

    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;
    }

    /** @return SalesOrder|null */
    public function getSalesOrder()
    {
        return $this->salesOrder;
    }

    /**
     * @param SalesOrder $order
     */
    public function setSalesOrder(SalesOrder $order)
    {
        assertion($order->getTotalAmountOutstanding() >= $this->getAmountCaptured(),
            "$order will be overpaid by $this");
        $this->salesOrder = $order;
        $this->customer = $order->getCustomer();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return SystemType|null
     */
    public function getSystemType()
    {
        return $this->systemType;
    }

    public function isType($type)
    {
        return $this->systemType->isType($type);
    }

    /**
     * @return int
     */
    public function getTransactionId()
    {
        return $this->transactionId;
    }

    /**
     * @return int
     */
    public function getSystemTypeNumber()
    {
        return $this->systemTypeNumber;
    }

    public function setAccountingTransaction(Transaction $trans)
    {
        $this->accountingTransaction = $trans;
        $this->systemType = $trans->getSystemType();
        $this->systemTypeNumber = $trans->getSystemTypeNumber();
    }

    public static function getSweepableTypes()
    {
        return [
            SystemType::RECEIPT,
            SystemType::CUSTOMER_REFUND,
        ];
    }

    public function isSweepable()
    {
        return $this->isCaptured()
            && (! $this->isVoid())
            && (! $this->isSettled())
            && $this->systemType
            && in_array($this->systemType->getId(), self::getSweepableTypes());
    }

    public function getDateCreated()
    {
        return $this->dateCreated ? clone $this->dateCreated : null;
    }

    /** @return DateTime */
    public function getPostDate()
    {
        return $this->postDate ? clone $this->postDate : null;
    }

    public function isSettled()
    {
        return $this->settled;
    }

    public function setSettled($posted)
    {
        $this->settled = $posted;
    }

    public function isVoid()
    {
        return $this->void;
    }

    public function setVoid($void)
    {
        $this->void = $void;
    }

    public function canBeRefunded()
    {
        return $this->isCaptured()
           && (! $this->isVoid());
    }

    public function __toString()
    {
        return sprintf('%s transaction %s',
            $this->creditCard,
            $this->transactionId);
    }

    public function getReferenceTransaction()
    {
        return $this->referenceTransaction;
    }

    public function setReferenceTransaction(CardTransaction $trans)
    {
        $this->referenceTransaction = $trans;
    }
}
