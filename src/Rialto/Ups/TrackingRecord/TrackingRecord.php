<?php

namespace Rialto\Ups\TrackingRecord;

use DateTime;
use Rialto\Database\Orm\Persistable;
use Rialto\Entity\RialtoEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Tracking record for one tracking number
 *
 * @UniqueEntity(fields={...},
 *   message="....")
 */
class TrackingRecord implements RialtoEntity, Persistable
{
    const STATUS_DELIVERED = 'delivered';
    const STATUS_UNDELIVERED = 'undelivered';

    /** @var string */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(message="Supplier reference must not be blank.")
     * @Assert\Length(max=50)
     * @Assert\Regex(pattern="/\d/",
     *   message="The supplier reference should probably contain some digits.")
     */
    private $trackingNumber;

    /**
     * @var DateTime
     * @Assert\NotNull(message="Record date is required.")
     * @Assert\DateTime(message="Record date is not valid.")
     * @Assert\Range(max="+2 years", maxMessage="Record date is too far in the future.")
     */
    private $dateCreated;

    /**
     * @var DateTime
     * @Assert\NotNull(message="Last Update date is required.")
     * @Assert\DateTime(message="Last Update date is not valid.")
     * @Assert\Range(max="+2 years", maxMessage="Last Update date is too far in the future.")
     */
    private $dateUpdated;

    /**
     * @var DateTime|null
     */
    private $dateDelivered;

    public function __construct(string $trackingNumber)
    {
        $this->trackingNumber = $trackingNumber;
        $this->dateCreated = new DateTime();
        $this->dateUpdated = new DateTime();
        $this->dateDelivered = null;
    }

    public function getEntities()
    {
        return [$this];
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTrackingNumber(): string
    {
        return $this->trackingNumber;
    }

    public function setTrackingNumber($trackingNumber)
    {
        $this->trackingNumber = $trackingNumber ?: '';
    }

    /**
     * Get dateCreated
     * @return DateTime
     */
    public function getDateCreated(): DateTime
    {
        return clone $this->dateCreated;
    }

    public function setDateUpdate()
    {
        $this->dateUpdated = new DateTime();
    }

    /**
     * Get dateUpdated
     * @return DateTime
     */
    public function getDateUpdated(): DateTime
    {
        return $this->dateUpdated;
    }

    public function setDateUpdated()
    {
        $this->dateUpdated = new DateTime();
    }

    /**
     * Get dateDelivered
     */
    public function getDateDelivered(): ?DateTime
    {
        return $this->dateDelivered;
    }

    public function setDateDelivered(?DateTime $date)
    {
        $this->dateDelivered = $date;
    }

    public function getTrackingStatus()
    {
        if ($this->dateDelivered === null) {
            return self::STATUS_UNDELIVERED;
        } else {
            return self::STATUS_DELIVERED;
        }
    }
}
