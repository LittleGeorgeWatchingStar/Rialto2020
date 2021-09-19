<?php

namespace Rialto\Accounting\Terms;

use DateTime;
use Rialto\Database\Orm\DbManager;
use Rialto\Entity\RialtoEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @UniqueEntity(fields={"id"}, message="That ID is already in use.")
 * @UniqueEntity(fields={"name"}, message="That name is already in use.")
 */
class PaymentTerms implements RialtoEntity
{
    const CC_PREPAID = 3;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(max=2)
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Length(max=40)
     */
    private $name;

    /**
     * @var int
     * @Assert\NotBlank
     * @Assert\Range(min=0, max=1000)
     */
    private $daysBeforeDue = 0;

    /**
     * @var int
     * @Assert\NotBlank
     * @Assert\Range(min=0, max=1000)
     */
    private $dayInFollowingMonth = 0;

    /** @return PaymentTerms */
    public static function findCreditCardPrepaid(DbManager $dbm)
    {
        return $dbm->need(self::class, self::CC_PREPAID);
    }

    public function __construct($id)
    {
        $this->id = trim($id);
    }

    public function getId()
    {
        return $this->id;
    }

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

    public function getDaysBeforeDue()
    {
        return $this->daysBeforeDue;
    }

    /**
     * @param int $days
     */
    public function setDaysBeforeDue($days)
    {
        $this->daysBeforeDue = $days;
    }

    public function getDayInFollowingMonth()
    {
        return $this->dayInFollowingMonth;
    }

    /**
     * @param int $day
     */
    public function setDayInFollowingMonth($day)
    {
        $this->dayInFollowingMonth = $day;
    }

    /** @return DateTime */
    public function calculateDueDate(DateTime $from)
    {
        $due = clone $from;
        $due->modify("+{$this->daysBeforeDue} days");
        return $due;
    }
}
