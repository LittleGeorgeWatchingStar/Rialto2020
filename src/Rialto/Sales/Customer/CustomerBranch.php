<?php

namespace Rialto\Sales\Customer;

use Gumstix\GeographyBundle\Model\Country;
use Gumstix\GeographyBundle\Model\PostalAddress;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Database\Orm\DbManager;
use Rialto\Email\Mailable\Mailable;
use Rialto\Entity\RialtoEntity;
use Rialto\Geography\Address\Address;
use Rialto\Sales\Salesman\Salesman;
use Rialto\Shipping\Shipper\Shipper;
use Rialto\Stock\Facility\Facility;
use Rialto\Tax\Authority\TaxAuthority;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A customer contact person.
 */
class CustomerBranch implements RialtoEntity, PostalAddress, Mailable
{
    /** @var integer */
    private $id;

    /** @var Customer */
    private $customer;

    /**
     * @deprecated
     * @var string
     * @Assert\Length(max=10)
     */
    private $branchCode = '';

    /**
     * @var string
     * @Assert\NotBlank(message="Branch name cannot be blank.")
     */
    private $branchName;

    /**
     * @var Address
     * @Assert\NotNull
     * @Assert\Valid
     */
    private $address;
    private $contactPhone = '';
    private $fax = '';
    private $contactName;

    /**
     * @Assert\NotBlank(message="Branch email cannot be blank.")
     * @Assert\Email
     */
    private $email = '';

    /**
     * @var TaxAuthority
     * @Assert\NotNull
     */
    private $taxAuthority;

    /**
     * An explanation of why this branch is exempt from denied party
     * screenings. Blank if not exempt.
     *
     * @var string
     * @Assert\Length(max=500)
     */
    private $deniedPartyExemption = '';
    private $customerBranchCode = '';

    /**
     * @var Salesman
     * @Assert\NotNull
     */
    private $salesman;

    /**
     * @var SalesArea
     * @Assert\NotNull
     */
    private $salesArea;

    /**
     * @var Facility
     * @Assert\NotNull
     */
    private $defaultLocation;

    /**
     * @var Shipper
     * @Assert\NotNull
     */
    private $defaultShipper;

    /**
     * Factory method.
     * @return CustomerBranch
     */
    public static function createWithDefaultValues(Customer $customer, DbManager $dbm)
    {
        $branch = new self($customer,
            TaxAuthority::fetchNoTax($dbm),
            $dbm->need(SalesArea::class, SalesArea::WORLDWIDE),
            $dbm->need(Facility::class, Facility::HEADQUARTERS_ID),
            $dbm->getRepository(Shipper::class)->findDefault());
        return $branch;
    }

    public function __construct(Customer $cust,
                                 TaxAuthority $authority,
                                 SalesArea $area,
                                 Facility $defaultLocation,
                                 Shipper $defaultShipper)
    {
        $this->customer = $cust;
        $this->taxAuthority = $authority;
        $this->salesArea = $area;
        $this->defaultLocation = $defaultLocation;
        $this->defaultShipper = $defaultShipper;
    }

    public function equals(CustomerBranch $other)
    {
        return $this->id == $other->id;
    }

    /**
     * @return Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return bool
     */
    public function isInternalCustomer()
    {
        return $this->customer->isInternalCustomer();
    }

    /**
     * @deprecated Use getSalesArea() instead.
     */
    public function getArea()
    {
        return $this->getSalesArea();
    }

    public function getId()
    {
        return $this->id;
    }

    /** @deprecated The branchCode field is deprecated */
    public function getBranchCode()
    {
        return $this->branchCode;
    }

    /** @deprecated The branchCode field is deprecated */
    public function setBranchCode($code)
    {
        $this->branchCode = trim($code);
        return $this;
    }

    public function getBranchName()
    {
        return $this->branchName;
    }

    public function getContactName()
    {
        return $this->contactName;
    }

    public function getLabel()
    {
        return sprintf('%s at %s',
            $this->contactName, $this->branchName);
    }

    public function __toString()
    {
        return $this->getLabel();
    }

    public function getContactPhone()
    {
        return $this->contactPhone;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getFax()
    {
        return $this->fax;
    }

    /**
     * @deprecated use getBranchName() instead
     */
    public function getName()
    {
        return $this->getBranchName();
    }

    /**
     * @return Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    public function setAddress(Address $address)
    {
        $this->address = $address;
    }

    public function getStreet1(): string
    {
        return $this->address->getStreet1();
    }

    public function getStreet2(): string
    {
        return $this->address->getStreet2();
    }

    public function getMailStop(): string
    {
        return $this->address->getMailStop();
    }

    public function getCity(): string
    {
        return $this->address->getCity();
    }

    public function getPostalCode(): string
    {
        return $this->address->getPostalCode();
    }

    public function getStateCode(): string
    {
        return $this->address->getStateCode();
    }

    public function getStateName(): string
    {
        return $this->address->getStateName();
    }

    /**
     * @return Country
     */
    public function getCountry()
    {
        return $this->address->getCountry();
    }

    public function getCountryCode(): string
    {
        return $this->address->getCountryCode();
    }

    public function getCountryName(): string
    {
        return $this->address->getCountryName();
    }

    /**
     * The customer's own internal code for this branch.
     * @return string
     */
    public function getCustomerBranchCode()
    {
        return $this->customerBranchCode;
    }

    public function getCustomerId()
    {
        return $this->customer->getId();
    }

    public function getDefaultLocation()
    {
        return $this->defaultLocation;
    }

    public function getDefaultShipper()
    {
        return $this->defaultShipper;
    }

    public function getTaxId()
    {
        return $this->customer->getTaxId();
    }

    public function getTaxAuthority(): TaxAuthority
    {
        return $this->taxAuthority;
    }

    public function getTaxAccount(): GLAccount
    {
        $ta = $this->getTaxAuthority();
        return $ta->getAccount();
    }

    /**
     * Resets the tax authority of this branch based on its address.
     */
    public function resetTaxAuthority(DbManager $dbm): self
    {
        $isCalifornia = ($this->getCountryCode() == 'US') &&
            (($this->getStateCode() == 'CA') ||
                (strtolower($this->getStateName()) == 'california'));

        if ($isCalifornia) {
            $this->taxAuthority = TaxAuthority::fetchCaStateTax($dbm);
        } else {
            $this->taxAuthority = TaxAuthority::fetchNoTax($dbm);
        }
        return $this;
    }

    public function isTaxExempt()
    {
        return $this->customer->isTaxExempt();
    }

    public function setBranchName($name)
    {
        $this->branchName = trim($name);
        return $this;
    }

    public function setContactName($name)
    {
        $this->contactName = trim($name);
        return $this;
    }

    /**
     * @todo: what is this and is it even used?
     */
    public function setCustomerBranchCode($code)
    {
        $this->customerBranchCode = trim($code);
        return $this;
    }

    public function setDefaultShipper(Shipper $shipper)
    {
        $this->defaultShipper = $shipper;
    }

    public function setContactPhone($phoneNo)
    {
        $this->contactPhone = trim($phoneNo);
        return $this;
    }

    public function setFax($faxNo)
    {
        $this->fax = trim($faxNo);
        return $this;
    }

    public function setEmail($email)
    {
        $this->email = trim($email);
        return $this;
    }

    public function setTaxAuthority(TaxAuthority $tax = null)
    {
        $this->taxAuthority = $tax;
        if (!$tax) {
            $this->taxAuthority = TaxAuthority::fetchNoTax();
        }
        return $this;
    }

    public function getSalesman()
    {
        return $this->salesman;
    }

    public function setSalesman(Salesman $salesman)
    {
        $this->salesman = $salesman;
    }

    public function getSalesArea()
    {
        return $this->salesArea;
    }

    public function setSalesArea(SalesArea $area)
    {
        $this->salesArea = $area;
    }

    public function setDefaultLocation(Facility $location)
    {
        $this->defaultLocation = $location;
    }

    public function isNew()
    {
        return !$this->id;
    }

    public function isDeniedPartyExempt()
    {
        return (bool) trim($this->deniedPartyExemption);
    }

    public function getDeniedPartyExemption()
    {
        return $this->deniedPartyExemption;
    }

    public function setDeniedPartyExemption($reason)
    {
        $this->deniedPartyExemption = trim($reason);
    }

}
