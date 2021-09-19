<?php

namespace Rialto\Payment\PaymentMethod;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Entity\RialtoEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Represents a payment method, such as credit card, PayPal, or cheque.
 *
 * @UniqueEntity(fields={"id"})
 */
class PaymentMethod implements RialtoEntity
{
    const ID_VISA = 'VISA';
    const ID_MASTERCARD = 'MCRD';
    const ID_AMEX = 'AMEX';
    const ID_DISCOVER = 'DISC';
    const ID_UNKNOWN = 'UNKN';

    /**
     * @var string
     * @Assert\Length(max=4,
     *   maxMessage="The ID cannot be longer than four characters.")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(max=50)
     */
    private $name = '';

    /**
     * @var PaymentMethodGroup
     * @Assert\NotNull
     */
    private $group;

    public function __construct($id)
    {
        $this->id = trim($id);
        assert($this->id != '');
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the name of this payment method; eg, "MasterCard", "PayPal".
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = trim($name);
    }

    public function __toString()
    {
        return $this->getName();
    }

    /** @return PaymentMethodGroup */
    public function getGroup()
    {
        return $this->group;
    }

    public function setGroup(PaymentMethodGroup $group)
    {
        $this->group = $group;
    }

    /**
     * The base fee is the flat amount charged by the provider of this
     * payment method, independent of the amount of the purchase.
     *
     * @return float
     */
    public function getBaseFee()
    {
        return $this->group->getBaseFee();
    }

    /**
     * The fee rate is the component of total fees that depends on the
     * amount of the purchase.
     *
     * @return float
     */
    public function getFeeRate()
    {
        return $this->group->getFeeRate();
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
        return $this->group->getTotalFees($amount);
    }

    public function equals(PaymentMethod $other = null)
    {
        if ( $other === null ) return false;
        return $this->id == $other->getId();
    }

    /** @return GLAccount */
    public function getDepositAccount()
    {
        return $this->group->getDepositAccount();
    }

    /** @return string */
    public function getDepositAccountName()
    {
        return $this->group->getDepositAccountName();
    }

    /** @return GLAccount */
    public function getFeeAccount()
    {
        return $this->group->getFeeAccount();
    }
}
