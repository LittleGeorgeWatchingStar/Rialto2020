<?php

namespace Rialto\Purchasing\Supplier;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Gumstix\GeographyBundle\Model\Country;
use Rialto\Accounting\Currency\Currency;
use Rialto\Accounting\Terms\PaymentTerms;
use Rialto\Database\Orm\ErpDbManager;
use Rialto\Entity\RialtoEntity;
use Rialto\Geography\Address\Address;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Manufacturer\Manufacturer;
use Rialto\Purchasing\Supplier\Attribute\SupplierAttribute;
use Rialto\Purchasing\Supplier\Contact\SupplierContact;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;
use Rialto\Tax\Authority\TaxAuthority;
use Rialto\Web\DomainName;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A supplier is a company that provides stock or services.
 */
class Supplier implements RialtoEntity
{
    private $id;

    /**
     * @var string
     * @Assert\Length(max="50")
     * @Assert\NotBlank(message="Supplier name cannot be blank.")
     */
    private $name;

    /**
     * @var Address
     * @Assert\NotNull
     * @Assert\Valid
     */
    private $orderAddress;

    /**
     * @var Address
     * @Assert\NotNull
     * @Assert\Valid
     */
    private $paymentAddress;

    /**
     * @var DateTime
     */
    private $supplierSince;

    /**
     * Our account number with this supplier.
     * @var string
     * @Assert\Length(max="20")
     */
    private $customerAccount = '';

    /**
     * A "secondary" account number, if applicable.
     * @var string
     * @Assert\Length(max="20")
     */
    private $customerNumber = '';
    private $lastPaid = 0.0;
    private $lastPaidDate;

    /**
     * @var string
     * @Assert\Length(max="16")
     */
    private $bankAccount = '';

    /**
     * @var string
     * @Assert\Length(max="12")
     */
    private $bankReference = '';

    /**
     * @var string
     * @Assert\Length(max="12")
     */
    private $bankParticulars = '';
    private $remittanceAdviceRequired = true;

    /**
     * @var string
     * @Assert\Length(max="31")
     */
    private $website = '';

    /**
     * Contract manufacturers (CMs) will have a stock facility.
     *
     * @var Facility|null
     */
    private $facility;

    private $currency;
    private $paymentTerms;
    private $contacts;
    private $taxAuthority;

    /**
     * The parent company that owns this supplier.
     * @var Supplier
     */
    private $parent;

    /**
     * If this supplier is also a manufacturer, this will be the corresponding
     * manufacturer.
     *
     * @var Manufacturer|null
     */
    private $manufacturer;

    /** @var SupplierAttribute[] */
    private $attributes;

    public function __construct(TaxAuthority $ta = null)
    {
        $this->taxAuthority = $ta ?: TaxAuthority::fetchNoTax();
        $this->supplierSince = new DateTime();
        $this->contacts = new ArrayCollection();
        $this->attributes = new ArrayCollection();
    }

    /**
     * @param StockItem stockItem
     * @return PurchasingData
     */
    public function createPurchasingData(StockItem $stockItem)
    {
        $purchData = new PurchasingData($stockItem);
        $purchData->setSupplier($this);
        return $purchData;
    }

    /**
     * Returns all contacts for this supplier.
     *
     * @return SupplierContact[]
     */
    public function getContacts()
    {
        return $this->contacts->getValues();
    }

    /**
     * @return SupplierContact[]
     */
    public function getActiveContacts()
    {
        return $this->contacts->filter(function (SupplierContact $c) {
            return $c->isActive();
        })->getValues();
    }

    /** @return SupplierContact[] */
    public function getKitContacts()
    {
        return $this->contacts->filter(function (SupplierContact $c) {
            return $c->isActive() && $c->isContactForKits();
        })->getValues();
    }

    /**
     * Returns those contacts who are involved in creating purchase orders.
     *
     * @return SupplierContact[]
     */
    public function getOrderContacts()
    {
        return $this->contacts->filter(function (SupplierContact $c) {
            return $c->isActive() && $c->isContactForOrders();
        })->getValues();
    }

    /**
     * @return null|Facility Not all suppliers have a location.
     */
    public function getFacility()
    {
        return $this->facility;
    }

    public function setFacility(Facility $facility)
    {
        $this->facility = $facility;
        return $this;
    }

    /**
     * @deprecated use getFacility() instead
     */
    public function getLocation()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFacility();
    }

    /**
     * @deprecated use setFacility() instead
     */
    public function setLocation(Facility $facility)
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        $this->facility = $facility;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * True if $other is the same supplier as this.
     *
     * @return boolean
     */
    public function equals(Supplier $other = null)
    {
        return $other && ($this->id == $other->id);
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(Supplier $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * True if $other is a parent (or ancestor) of this, or
     * if they are the same company.
     * @return boolean
     */
    public function isSubsidiaryOf(Supplier $other = null)
    {
        $current = $this;
        while ($current !== null) {
            if ($current->equals($other)) {
                return true;
            }
            $current = $current->parent;
        }
        return false;
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
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency)
    {
        $this->currency = $currency;
    }

    public function getSupplierSince()
    {
        return $this->supplierSince ? $this->supplierSince : null;
    }

    public function setSupplierSince(DateTime $date)
    {
        $this->supplierSince = $date;
    }

    public function getCustomerNumber()
    {
        return $this->customerNumber;
    }

    public function setCustomerNumber($number)
    {
        $this->customerNumber = trim($number);
    }

    public function getCustomerAccount()
    {
        return $this->customerAccount;
    }

    public function setCustomerAccount($account)
    {
        $this->customerAccount = trim($account);
    }

    public function getBankAccount()
    {
        return $this->bankAccount;
    }

    public function setBankAccount($account)
    {
        $this->bankAccount = trim($account);
    }

    public function getBankReference()
    {
        return $this->bankReference;
    }

    public function setBankReference($ref)
    {
        $this->bankReference = trim($ref);
    }

    public function getBankParticulars()
    {
        return $this->bankParticulars;
    }

    public function setBankParticulars($particulars)
    {
        $this->bankParticulars = trim($particulars);
    }

    public function isRemittanceAdviceRequired()
    {
        return (bool) $this->remittanceAdviceRequired;
    }

    public function setRemittanceAdviceRequired($remittance)
    {
        $this->remittanceAdviceRequired = $remittance;
    }

    public function getTaxAuthority()
    {
        return $this->taxAuthority;
    }

    public function setTaxAuthority(TaxAuthority $auth = null)
    {
        $this->taxAuthority = $auth ? $auth : TaxAuthority::fetchNoTax();
    }

    public function getWebsite()
    {
        return $this->website;
    }

    public function setWebsite($website)
    {
        $this->website = trim($website);
    }

    public function getDomainName()
    {
        if (!$this->website) {
            return null;
        }
        return DomainName::parse($this->website);
    }

    public function getSearchUrl($query = null)
    {
        if ($this->hasAttribute(SupplierAttribute::SEARCH_URL)) {
            $pattern = $this->getAttributeValue(SupplierAttribute::SEARCH_URL);
            return $query
                ? str_replace(':q', urlencode($query), $pattern)
                : $pattern;
        }
        return null;
    }

    /**
     * @param string $name
     * @return bool
     */
    private function hasAttribute($name)
    {
        return null != $this->getAttributeOrNull($name);
    }

    /**
     * @param string $name The attribute name
     * @return string The attribute value
     */
    private function getAttributeValue($name)
    {
        $att = $this->getAttributeOrNull($name);
        if ($att) {
            return $att->getValue();
        }
        throw new \InvalidArgumentException("$this has no such attribute '$name'");
    }

    /**
     * @param string $name
     * @return SupplierAttribute|null
     */
    private function getAttributeOrNull($name)
    {
        foreach ($this->attributes as $att) {
            if ($att->isAttribute($name)) {
                return $att;
            }
        }
        return null;
    }

    /** @return PaymentTerms */
    public function getPaymentTerms()
    {
        return $this->paymentTerms;
    }

    public function setPaymentTerms(PaymentTerms $terms)
    {
        $this->paymentTerms = $terms;
    }

    /**
     * @param Item $item
     * @return PurchasingData
     */
    public function getPurchasingData(Item $item)
    {
        $dbm = ErpDbManager::getInstance();
        $mapper = $dbm->getRepository(PurchasingData::class);
        return $mapper->findPreferredBySupplier($this, $item);

    }

    public function setLastPaid($amount, DateTime $date = null)
    {
        $this->lastPaid = $amount;
        $this->lastPaidDate = $date ?: new DateTime();
    }

    /** @return Address|null */
    public function getOrderAddress()
    {
        return $this->orderAddress;
    }

    public function setOrderAddress(Address $address)
    {
        $this->orderAddress = $address;
    }

    /** @return Address|null */
    public function getPaymentAddress()
    {
        return $this->paymentAddress;
    }

    public function setPaymentAddress(Address $address)
    {
        $this->paymentAddress = $address;
    }

    public function getOrderAddressStreet1(): string
    {
        return $this->orderAddress->getStreet1();
    }

    public function getOrderAddressStreet2(): string
    {
        return $this->orderAddress->getStreet2();
    }

    public function getOrderAddressMailStop(): string
    {
        return $this->orderAddress->getMailStop();
    }

    public function getOrderAddressCity(): string
    {
        return $this->orderAddress->getCity();
    }

    public function getOrderAddressStateCode(): string
    {
        return $this->orderAddress->getStateCode();
    }

    public function getOrderAddressStateName(): string
    {
        return $this->orderAddress->getStateName();
    }

    public function getOrderAddressPostalCode(): string
    {
        return $this->orderAddress->getPostalCode();
    }

    /**
     * @return Country
     */
    public function getOrderAddressCountry()
    {
        return $this->orderAddress->getCountry();
    }

    public function getOrderAddressCountryCode(): string
    {
        return $this->orderAddress->getCountryCode();
    }

    public function getCountryName(): string
    {
        return $this->orderAddress->getCountryName();
    }

    public function getPaymentAddressStreet1(): string
    {
        return $this->paymentAddress->getStreet1();
    }

    public function getPaymentAddressStreet2(): string
    {
        return $this->paymentAddress->getStreet2();
    }

    public function getPaymentAddressMailStop(): string
    {
        return $this->paymentAddress->getMailStop();
    }

    public function getPaymentAddressCity(): string
    {
        return $this->paymentAddress->getCity();
    }

    public function getPaymentAddressStateCode(): string
    {
        return $this->paymentAddress->getStateCode();
    }

    public function getPaymentAddressStateName(): string
    {
        return $this->paymentAddress->getStateName();
    }

    public function getPaymentAddressPostalCode(): string
    {
        return $this->paymentAddress->getPostalCode();
    }

    /**
     * @return Country
     */
    public function getPaymentAddressCountry()
    {
        return $this->paymentAddress->getCountry();
    }
    public function getPaymentAddressCountryCode(): string
    {
        return $this->paymentAddress->getCountryCode();
    }
    public function getPaymentAddressCountryName(): string
    {
        return $this->paymentAddress->getCountryName();
    }

    /**
     * @return Manufacturer|null
     */
    public function getManufacturer()
    {
        return $this->manufacturer;
    }
}