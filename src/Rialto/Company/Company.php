<?php

namespace Rialto\Company;

use Doctrine\ORM\EntityManagerInterface;
use Gumstix\GeographyBundle\Model\BasicAddress;
use Gumstix\GeographyBundle\Model\Country;
use Gumstix\GeographyBundle\Model\PostalAddress;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Database\Orm\DbManager;
use Rialto\Database\Orm\ErpDbManager;
use Rialto\Email\Mailable\Mailable;
use Rialto\Entity\RialtoEntity;

/**
 * The company to which this ERP system belongs.
 */
class Company implements RialtoEntity, Mailable
{
    /**************\
     STATIC MEMBERS
    \**************/

    const DEFAULT_ID = 1;

    /**
     * @static
     * @return Company
     */
    public static function fetchDefault()
    {
        return self::findDefault(ErpDbManager::getInstance());
    }

    /** @return Company */
    public static function findDefault(DbManager $dbm)
    {
        return $dbm->need(self::class, self::DEFAULT_ID);
    }

    public static function getProxy(EntityManagerInterface $em)
    {
        return $em->getReference(self::class, self::DEFAULT_ID);
    }

    /****************\
     INSTANCE MEMBERS
    \****************/

    private $id;
    private $companyName;
    private $regOffice1;
    private $regOffice2;
    private $regOffice3;
    private $email;
    private $doesDebtorAccounting;
    private $doesCreditorAccounting;
    private $doesStockAccounting;

    private $debtorAccount;
    private $grnAccount;
    private $glAccount;
    private $creditorsAccount;
    private $pytDiscountAccount;

    private $GSTNo;
    private $CompanyNumber;
    private $PostalAddress;
    private $Telephone;
    private $Fax;
    private $CurrencyDefault;
    private $PayrollAct;
    private $ExchangeDiffAct;
    private $PurchasesExchangeDiffAct;
    private $RetainedEarnings;

    /**
     * @return GLAccount
     */
    public function getDebtorAccount()
    {
        return $this->debtorAccount;
    }

    /**
     * @return GLAccount
     */
    public function getCreditorsAccount()
    {
        return $this->creditorsAccount;
    }

    /**
     * @deprecated Use getCreditorsAccount() instead.
     */
    public function getCreditorAccount()
    {
        return $this->getCreditorsAccount();
    }

    /**
     * @return GLAccount
     */
    public function getPaymentDiscountAccount()
    {
        return $this->pytDiscountAccount;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return GLAccount
     */
    public function getGrnAccount()
    {
        return $this->grnAccount;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->companyName;
    }

    public function setName(string $name)
    {
        $this->companyName = trim($name);
    }

    /**
     * @return GLAccount
     */
    public function getShippingAccount()
    {
        return $this->glAccount;
    }

    /**
     * @return string
     */
    public function getShortName()
    {
        $remove = [', inc'];
        return str_ireplace($remove, '', $this->companyName);
    }

    public function __toString()
    {
        return $this->getShortName();
    }


    /**
     * @deprecated We should always do all accounting.
     * @return bool
     */
    public function doesCreditorAccounting()
    {
        return (bool) $this->doesCreditorAccounting;
    }

    /**
     * @deprecated We should always do all accounting.
     * @return bool
     */
    public function doesDebtorAccounting()
    {
        return (bool) $this->doesDebtorAccounting;
    }

    /**
     * @deprecated We should always do all accounting.
     * @return bool
     */
    public function doesStockAccounting()
    {
        return (bool) $this->doesStockAccounting;
    }

    public function getRegOffice1(): string
    {
        return $this->regOffice1;
    }

    public function setRegOffice1(string $regOffice1): void
    {
        $this->regOffice1 = $regOffice1;
    }

    public function getRegOffice2(): string
    {
        return $this->regOffice2;
    }

    public function setRegOffice2(string $regOffice2): void
    {
        $this->regOffice2 = $regOffice2;
    }

    public function getRegOffice3(): string
    {
        return $this->regOffice3;
    }

    public function setRegOffice3(string $regOffice3): void
    {
        $this->regOffice3 = $regOffice3;
    }

    public function getAddress(): PostalAddress
    {
        $addr = new BasicAddress();

        /* Parse the Companies table's terrible address format. */
        $addr->setStreet1($this->regOffice1);
        list($city, $statePostalCode) = explode(',', $this->regOffice2);
        $statePostalCode = trim($statePostalCode);
        list($stateCode, $postalCode) = explode(' ', $statePostalCode);

        $countryCode = Country::resolveCountryCode($this->regOffice3);

        $addr->setCity( trim($city) );
        $addr->setStateCode( trim($stateCode) );
        $addr->setPostalCode( trim($postalCode) );
        if ( $countryCode ) {
            $addr->setCountry(new Country($countryCode));
        }
        return $addr;
    }
}
