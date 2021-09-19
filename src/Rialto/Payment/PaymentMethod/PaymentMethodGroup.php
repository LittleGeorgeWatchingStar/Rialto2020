<?php

namespace Rialto\Payment\PaymentMethod;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Entity\RialtoEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A group of payment methods that all share the same fee structure.
 *
 * @UniqueEntity(fields={"id"})
 */
class PaymentMethodGroup implements RialtoEntity
{
    const TYPE_CREDIT_CARD = 'credit card';

    /**
     * @var string
     * @Assert\Length(max=4,
     *   maxMessage="The ID cannot be longer than four characters.")
     */
    private $id;

    /** @var string */
    private $type = self::TYPE_CREDIT_CARD;

    /**
     * @var float
     * @Assert\NotBlank
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    private $baseFee;

    /**
     * @var float
     * @Assert\NotBlank
     * @Assert\Type(type="numeric")
     * @Assert\Range(
     *   min=0,
     *   max=1,
     *   maxMessage="Is the fee rate really that high?")
     */
    private $feeRate;

    /**
     * @var GLAccount
     * @Assert\NotNull
     */
    private $depositAccount;

    /**
     * @var GLAccount
     * @Assert\NotNull
     */
    private $feeAccount;

    /**
     * Whether the fees should automatically be withheld every day during
     * the card sweep.
     * @var bool
     */
    private $sweepFeesDaily = false;

    public function __construct($id)
    {
        $this->id = trim($id);
        assertion($this->id != '');
    }

    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    /** @return string[] */
    public static function getValidTypes()
    {
        return [
            self::TYPE_CREDIT_CARD => self::TYPE_CREDIT_CARD,
        ];
    }

    /**
     * The base fee is the flat amount charged by the provider of this
     * payment method, independent of the amount of the purchase.
     *
     * @return float
     */
    public function getBaseFee()
    {
        return $this->baseFee;
    }

    /**
     * @param float $baseFee
     */
    public function setBaseFee($baseFee)
    {
        $this->baseFee = $baseFee;
    }

    /**
     * The fee rate is the component of total fees that depends on the
     * amount of the purchase.
     *
     * @return float
     */
    public function getFeeRate()
    {
        return $this->feeRate;
    }

    /**
     * @param float $feeRate
     */
    public function setFeeRate($feeRate)
    {
        $this->feeRate = $feeRate;
    }

    /**
     * Returns the total amount of fees that would be charged on the
     * given amount.
     *
     * @param float $amount
     * @return float
     */
    public function getTotalFees($amount)
    {
        return $this->baseFee + ( $amount * $this->feeRate );
    }

    /**
     * @return GLAccount
     */
    public function getDepositAccount()
    {
        return $this->depositAccount;
    }

    public function setDepositAccount(GLAccount $account)
    {
        $this->depositAccount = $account;
    }

    public function getDepositAccountName()
    {
        return $this->depositAccount->getName();
    }

    /** @return GLAccount|null */
    public function getFeeAccount()
    {
        return $this->feeAccount;
    }

    public function setFeeAccount(GLAccount $account)
    {
        $this->feeAccount = $account;
    }

    /**
     * @return boolean
     */
    public function isSweepFeesDaily()
    {
        return $this->sweepFeesDaily;
    }

    /**
     * @param boolean $sweepFees
     */
    public function setSweepFeesDaily($sweepFees)
    {
        $this->sweepFeesDaily = $sweepFees;
    }
}
