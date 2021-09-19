<?php

namespace Rialto\Stock\Facility;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\NoResultException;
use Gumstix\GeographyBundle\Model\PostalAddress;
use Rialto\Email\Mailable\GenericMailable;
use Rialto\Email\Mailable\Mailable;
use Rialto\Entity\RialtoEntity;
use Rialto\Geography\Address\Address;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Location;
use Rialto\Tax\Authority\TaxAuthority;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Any business or building where our company's stock can be stored.
 */
class Facility implements RialtoEntity, PostalAddress, Location
{
    /**************\
     STATIC MEMBERS
    \**************/

    const HEADQUARTERS_ID = 7;
    const TESTING_ID = 13;
    const IN_TRANSIT_ID = 'TRANS';

    /**
     * Fetches the Location object whose LocCode is given.
     *
     * @static
     * @param string $LocCode
     *        The ID of the location to fetch.
     * @return Facility
     */
    private static function fetch($id, ObjectManager $dbm)
    {
        /** @var Facility $location */
        $location = $dbm->find(self::class, $id);
        if ($location) {
            return $location;
        }
        throw new NoResultException();
    }

    /**
     * Fetches the Location record for the company headquarters.
     *
     * @static
     * @return Facility
     */
    public static function fetchHeadquarters(ObjectManager $om)
    {
        return self::fetch(self::HEADQUARTERS_ID, $om);
    }

    /**
     * @return Facility
     *  The location where products are stored while they await testing.
     */
    public static function fetchProductTesting(ObjectManager $dbm)
    {
        return self::fetch(self::TESTING_ID, $dbm);
    }

    /****************\
     INSTANCE MEMBERS
    \****************/

    /**
     * @var string
     * @Assert\Length(
     *  max="5",
     *  maxMessage="The location code cannot be longer than five characters.")
     */
    private $id;

    /**
     * The parent location, if this is a sub-location (ie, is within
     * another location)
     * @var Facility|null
     */
    private $parentLocation = null;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $name;

    /**
     * @var Address|null
     * @Assert\Valid
     */
    private $address = null;

    private $phone = '';
    private $fax = '';

    /**
     * @var string
     * @Assert\Email
     */
    private $email = '';
    private $contactName = '';
    private $supplier = null;

    private $active = true;
    private $allocateFromCM = 0;
    private $taxAuthority;

    private $new = false;


    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Returns the LocCode of this location.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the name of this stock location.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = trim($name);
    }

    public function __toString()
    {
        return $this->name;
    }

    /** @return PostalAddress */
    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress(Address $address = null)
    {
        $this->address = $address;
    }

    public function getStreet1(): string
    {
        return $this->address ? $this->address->getStreet1() : '';
    }

    public function getStreet2(): string
    {
        return $this->address ? $this->address->getStreet2() : '';
    }

    public function getMailStop(): string
    {
        return $this->address ? $this->address->getMailStop() : '';
    }

    public function getCity(): string
    {
        return $this->address ? $this->address->getCity() : '';
    }

    public function getStateName(): string
    {
        return $this->address ? $this->address->getStateName() : '';
    }

    public function getStateCode(): string
    {
        return $this->address ? $this->address->getStateCode() : '';
    }

    public function getPostalCode(): string
    {
        return $this->address ? $this->address->getPostalCode() : '';
    }

    public function getCountryCode(): string
    {
        return $this->address ? $this->address->getCountryCode() : '';
    }

    public function getCountryName(): string
    {
        return $this->address ? $this->address->getCountryName() : '';
    }

    /**
     * @return Supplier|null
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /** @return boolean */
    public function hasSupplier()
    {
        return (bool) $this->supplier;
    }

    public function setSupplier(Supplier $supplier = null)
    {
        $this->supplier = $supplier;
        if ($supplier) {
            $supplier->setFacility($this);
        }
    }

    public function getTaxAuthority()
    {
        return $this->taxAuthority;
    }

    public function setTaxAuthority(TaxAuthority $auth = null)
    {
        $this->taxAuthority = $auth;
    }

    public function getPhone()
    {
        return $this->phone;
    }

    public function setPhone($phone)
    {
        $this->phone = trim($phone);
    }

    public function getFax()
    {
        return $this->fax;
    }

    public function setFax($fax)
    {
        $this->fax = trim($fax);
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = trim($email);
    }

    public function getContactName()
    {
        return $this->contactName;
    }

    public function setContactName($contactName)
    {
        $this->contactName = trim($contactName);
    }

    /** @return Mailable */
    public function getContact()
    {
        return new GenericMailable($this->email, $this->contactName);
    }

    public function isActive()
    {
        return (bool) $this->active;
    }

    public function setActive($active)
    {
        $this->active = $active;
    }

    public function isAllocateFromCM()
    {
        return (bool) $this->allocateFromCM;
    }

    public function equals(Location $other = null)
    {
        return ($other instanceof Facility)
            ? ($this->id == $other->id)
            : false;
    }

    /** @return Facility|null */
    public function getParentLocation()
    {
        return $this->parentLocation;
    }

    public function setParentLocation(Facility $parentLocation)
    {
        $this->parentLocation = $parentLocation;
    }

    /** @Assert\Callback() */
    public function validateParentLocation(ExecutionContextInterface $context)
    {
        if ($this->equals($this->parentLocation)) {
            $context->buildViolation("A location cannot be its own parent.")
                ->atPath('parentLocation')
                ->addViolation();
        } elseif ($this->parentLocation && $this->parentLocation->parentLocation) {
            $context->buildViolation("%parent% cannot be both a parent and child location.")
                ->setParameter('%parent%', $this->parentLocation)
                ->atPath('parentLocation')
                ->addViolation();
        }
    }

    /**
     * True if this location is a sub-location of $other, or vice-versa.
     * @return boolean
     */
    public function isColocatedWith(Facility $other = null)
    {
        if (! $other ) {
            return false;
        }
        return $this->isSublocationOf($other) || $other->isSublocationOf($this);
    }

    /**
     * @param Facility|null $other
     * @return bool True if this location is $other or is a sublocation of $other.
     */
    public function isSublocationOf(Facility $other = null)
    {
        if (! $other) {
            return false;
        }
        $current = $this;
        while ( $current ) {
            if ( $current->equals($other) ) return true;
            $current = $current->getParentLocation();
        }
        return false;
    }

    public function setAllocateFromCM($allocate)
    {
        $this->allocateFromCM = $allocate;
    }


    /**
     * @return boolean
     */
    public function isHeadquarters()
    {
        return self::HEADQUARTERS_ID == $this->id;
    }

    /**
     * @return boolean
     */
    public function isProductTesting()
    {
        return self::TESTING_ID == $this->id;
    }

    /**
     * @return bool True if this location can supply parts to $other.
     */
    public function canSupply(Facility $other)
    {
        if ($this->isHeadquarters()) {
            return true;
        }
        return $this->equals($other);
    }

    public function isNew()
    {
        return $this->new;
    }

    public function setNew()
    {
        $this->new = true;
    }
}

