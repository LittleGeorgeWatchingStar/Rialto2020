<?php

namespace Rialto\Sales\Customer;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Gumstix\GeographyBundle\Model\Country;
use Gumstix\GeographyBundle\Model\PostalAddress;
use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Terms\PaymentTerms;
use Rialto\Database\Orm\DbManager;
use Rialto\Email\Mailable\Mailable;
use Rialto\Entity\RialtoEntity;
use Rialto\Geography\Address\Address;
use Rialto\Sales\Type\SalesType;
use Rialto\Tax\TaxExemption;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * A customer is a person or organization who buys products from us.
 *
 * A customer has one or more branches, which roughly correspond to
 * office locations or contact people.
 */
class Customer implements
    RialtoEntity,
    PostalAddress,
    Mailable
{
    /**
     * Factory method.
     *
     * @return Customer
     */
    public static function createWithDefaultValues(DbManager $dbm)
    {
        $currency = Currency::findUSD($dbm);
        $reason = HoldReason::findGoodCreditStatus($dbm);
        $terms = PaymentTerms::findCreditCardPrepaid($dbm);
        return new self($currency, $reason, $terms);
    }

    /**
     * Returns a list of important words in the given company name.
     *
     * Removes things like "Ltd", "Corp", etc.
     * @param string $name
     * @return string[]
     */
    public static function getKeywordsFromName($name)
    {
        // Note the intentional unicode handling.
        $name = preg_replace('/[^\w\-]/u', ' ', $name); // remove non-unicode word characters
        $name = trim($name);
        $words = preg_split('/\s+/', $name);

        static $exclude = [
            'corp',
            'corporation',
            'inc',
            'limited',
            'llc',
            'ltd',
        ];
        $isImportantWord = function ($word) use ($exclude) {
            return (!in_array(strtolower($word), $exclude))
                && (mb_strlen($word, 'UTF-8') > 2);
        };
        return array_values(array_filter($words, $isImportantWord));
    }

    private $id;

    /**
     * @Assert\NotBlank(message="Customer name cannot be blank.")
     * @Assert\Length(max=255, maxMessage="Customer name is too long.")
     */
    private $name;

    /**
     * @Assert\NotBlank(message="Company name cannot be blank.")
     * @Assert\Length(max=255, maxMessage="Company name is too long.")
     */
    private $companyName;

    /**
     * @var Address
     * @Assert\NotNull
     * @Assert\Valid
     */
    private $address;

    /**
     * @var string
     * @Assert\Length(max=100, maxMessage="Tax ID is too long.")
     */
    private $taxId = '';
    private $customerSince;
    private $discountRate = 0;
    private $PymtDiscount;
    private $LastPaid = 0;
    private $LastPaidDate;
    private $creditLimit = 1000;
    private $addressedAtBranch = 0;
    private $DiscountCode;
    private $EDIInvoices;
    private $EDIOrders;

    /**
     * @Assert\Length(max=20, maxMessage="EDI reference is too long.")
     */
    private $EDIReference = '';
    private $EDITransport;

    /**
     * @Assert\NotBlank(message="Customer email cannot be blank.")
     * @Assert\Length(max=255, maxMessage="Email is too long.")
     * @Assert\Email
     */
    private $email = '';
    private $EDIServerUser;
    private $EDIServerPwd;
    private $taxExemptionStatus = TaxExemption::NONE;

    /**
     * @Assert\Length(max=50, maxMessage="Tax exemption number is too long.")
     */
    private $taxExemptionNumber = '';
    private $internalCustomer = false;

    /** @var PaymentTerms */
    private $paymentTerms;

    /** @var Currency */
    private $currency;

    /** @var SalesType */
    private $salesType;

    /** @var HoldReason */
    private $holdReason;

    /**
     * @var ArrayCollection<CustomerBranch>
     */
    private $branches;

    public function __construct(Currency $currency, HoldReason $reason, PaymentTerms $terms)
    {
        $this->customerSince = new DateTime();
        $this->branches = new ArrayCollection();
        $this->currency = $currency;
        $this->holdReason = $reason;
        $this->paymentTerms = $terms;
    }

    /**
     * @deprecated The branchCode field is deprecated
     *
     * Returns the branch whose code is given.
     *
     * @param $branchCode
     * @return CustomerBranch|null
     *  Null if there is no matching branch for this customer.
     */
    public function getBranch($branchCode)
    {
        foreach ($this->branches as $branch) {
            if ($branch->getBranchCode() == $branchCode) {
                return $branch;
            }
        }
        return null;
    }

    /**
     * Returns all of this customer's branches (ie, addresses).
     *
     * @return CustomerBranch[]
     */
    public function getBranches()
    {
        return $this->branches->toArray();
    }

    public function getCompanyName()
    {
        return $this->companyName;
    }

    public function getId()
    {
        return $this->id;
    }

    /** @return bool */
    public function equals(Customer $other = null)
    {
        return $other && ($other->getId() == $this->id);
    }

    /** @return Address */
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

    public function getStateCode(): string
    {
        return $this->address->getStateCode();
    }

    public function getStateName(): string
    {
        return $this->address->getStateName();
    }

    public function getPostalCode(): string
    {
        return $this->address->getPostalCode();
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

    public function getCreditLimit()
    {
        return $this->creditLimit;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function getCustomerName()
    {
        return $this->name ? $this->name : $this->companyName;
    }

    public function __toString()
    {
        return $this->getCustomerName();
    }

    /** @return DateTime */
    public function getDateCreated()
    {
        return $this->getCustomerSince();
    }

    public function getCustomerSince()
    {
        return clone $this->customerSince;
    }

    public function getDiscountRate()
    {
        return $this->discountRate;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getHoldReason()
    {
        return $this->holdReason;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPaymentTerms()
    {
        return $this->paymentTerms;
    }

    /**
     * @return SalesType
     */
    public function getSalesType()
    {
        return $this->salesType;
    }

    /**
     * @return string
     */
    public function getTaxId()
    {
        return $this->taxId;
    }

    public function getTaxExemptionNumber()
    {
        return $this->taxExemptionNumber;
    }

    /**
     * Eg, Federal, Resale, etc.
     * @return string
     */
    public function getTaxExemptionStatus()
    {
        return $this->taxExemptionStatus;
    }

    public function isAddressedAtBranch()
    {
        return $this->addressedAtBranch;
    }

    public function isTaxExempt()
    {
        return TaxExemption::isExempt($this->taxExemptionStatus);
    }

    public function setAddressedAtBranch($bool)
    {
        $this->addressedAtBranch = (bool) $bool;
        return $this;
    }

    public function setName($name)
    {
        $this->name = trim($name);
    }

    public function setCompanyName($name)
    {
        $this->companyName = trim($name);
        return $this;
    }

    public function setCreditLimit($limit)
    {
        $this->creditLimit = (float) $limit;
        return $this;
    }

    public function setCurrency(Currency $curr)
    {
        $this->currency = $curr;
        return $this;
    }

    /** @deprecated Use setName() instead */
    public function setCustomerName($name)
    {
        $this->setName($name);
    }

    public function setCustomerSince(DateTime $date)
    {
        $this->customerSince = $date;
        return $this;
    }

    public function setDiscountRate($rate)
    {
        $this->discountRate = (float) $rate;
        return $this;
    }

    public function setEDIReference($ref)
    {
        $this->EDIReference = trim($ref);
        return $this;
    }

    public function setEmail($email)
    {
        $this->email = trim($email);
        return $this;
    }

    public function setPaymentTerms(PaymentTerms $terms)
    {
        $this->paymentTerms = $terms;
        return $this;
    }

    public function setSalesType(SalesType $type)
    {
        $this->salesType = $type;
        return $this;
    }

    /**
     * @return Customer
     *  Fluent interface
     */
    public function setTaxId($taxId)
    {
        $this->taxId = trim($taxId);
        return $this;
    }

    /**
     * @return Customer
     *  Fluent interface
     */
    public function setTaxExemptionNumber($exemptionNo)
    {
        $this->taxExemptionNumber = trim($exemptionNo);
        return $this;
    }

    /**
     * @return Customer
     *  Fluent interface
     */
    public function setTaxExemptionStatus($status)
    {
        $this->taxExemptionStatus = trim($status);
        return $this;
    }

    public function isInternalCustomer()
    {
        return $this->internalCustomer;
    }

    public function setInternalCustomer($internal)
    {
        $this->internalCustomer = (bool) $internal;
    }
}

