<?php

namespace Rialto\Payment;

use DateTime;
use Rialto\Accounting\Money;
use Rialto\Payment\PaymentMethod\PaymentMethod;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * The information required to authorize a credit card.
 */
class CardAuth
{
    /**
     * @var PaymentMethod
     * @Assert\NotNull
     */
    private $type;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\CardScheme(schemes={"AMEX", "DISCOVER", "MASTERCARD", "VISA"})
     */
    private $number;

    /**
     * @var DateTime
     * @Assert\NotNull
     * @Assert\GreaterThanOrEqual("today", message="This card has expired.")
     */
    private $expiry;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $code;

    /**
     * @var float
     * @Assert\Range(min=0.01)
     */
    private $amount;

    /**
     * @var float The maximum amount that can be authorized.
     */
    private $maximum;

    public function __construct($maximum)
    {
        $this->maximum = $maximum;
        $this->amount = $maximum;
    }

    /** @return PaymentMethod */
    public function getType()
    {
        return $this->type;
    }

    public function setType(PaymentMethod $type)
    {
        $this->type = $type;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public function setNumber($number)
    {
        $this->number = trim($number);
    }

    public function getExpiry()
    {
        return $this->expiry ? clone $this->expiry : null;
    }

    public function setExpiry(DateTime $expiry)
    {
        $this->expiry = clone $expiry;
    }

    /** @return string */
    public function formatExpiry($format)
    {
        return $this->expiry->format($format);
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    /**
     * @Assert\Callback
     */
    public function validateMaximum(ExecutionContextInterface $context)
    {
        if (Money::round($this->amount) > Money::round($this->maximum)) {
            $context->buildViolation("Cannot authorize more than _max.")
                ->atPath('amount')
                ->setParameter('_max', number_format($this->maximum, 2))
                ->addViolation();
        }
    }
}
