<?php

namespace Rialto\Purchasing\Supplier\Contact;

use Rialto\Email\Mailable\Mailable;
use Rialto\Entity\RialtoEntity;
use Rialto\Purchasing\Supplier\Supplier;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A contact employee for a supplier.
 */
class SupplierContact implements RialtoEntity, Mailable
{
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(message="Contact name cannot be blank.")
     * @Assert\Length(max=30)
     */
    private $name;

    /**
     * @var string
     * @Assert\Length(max=30)
     */
    private $position = '';

    /**
     * @var string
     * @Assert\Length(max=30)
     */
    private $phone = '';

    /**
     * @var string
     * @Assert\Length(max=30)
     */
    private $fax = '';

    /**
     * @var string
     * @Assert\Length(max=30)
     */
    private $mobilePhone = '';

    /**
     * @var string
     * @Assert\Length(max=55)
     */
    private $email = '';
    private $contactForOrders = 0;
    private $contactForStats = 0;
    private $contactForKits = 0;

    private $supplier;

    /** @var bool */
    private $active = true;

    /**
     * Factory function.
     *
     * @param Supplier $supp
     * @return self
     */
    public static function create(Supplier $supp)
    {
        $contact = new self();
        $contact->supplier = $supp;
        return $contact;
    }

    public function getId()
    {
        return $this->id;
    }

    /** @return Supplier */
    public function getSupplier()
    {
        return $this->supplier;
    }

    public function isForSupplier(Supplier $s)
    {
        return $s->equals($this->supplier);
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = trim($name);
        return $this;
    }

    public function __toString()
    {
        return $this->name;
    }

    public function getPosition()
    {
        return $this->position;
    }

    public function setPosition($pos)
    {
        $this->position = trim($pos);
        return $this;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = trim($phone);
        return $this;
    }

    public function getFax()
    {
        return $this->fax;
    }

    public function setFax($fax)
    {
        $this->fax = trim($fax);
        return $this;
    }

    public function getMobilePhone()
    {
        return $this->mobilePhone;
    }

    public function setMobilePhone($mobile)
    {
        $this->mobilePhone = trim($mobile);
        return $this;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = trim($email);
        return $this;
    }

    public function getEmailLabel()
    {
        return sprintf('%s <%s>',
            $this->getName(),
            $this->getEmail()
        );
    }

    public function getQuoteLabel()
    {
        return sprintf('%s - %s',
            $this->supplier,
            $this->getEmailLabel());
    }

    public function isContactForOrders()
    {
        return (bool) $this->contactForOrders;
    }

    public function setContactForOrders($bool)
    {
        $this->contactForOrders = (bool) $bool;
        return $this;
    }

    public function isContactForStats()
    {
        return (bool) $this->contactForStats;
    }

    public function setContactForStats($bool)
    {
        $this->contactForStats = (bool) $bool;
        return $this;
    }

    public function isContactForKits()
    {
        return (bool) $this->contactForKits;
    }

    public function setContactForKits($bool)
    {
        $this->contactForKits = $bool;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active)
    {
        $this->active = $active;
    }
}
