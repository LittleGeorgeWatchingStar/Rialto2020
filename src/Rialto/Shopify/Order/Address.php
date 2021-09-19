<?php

namespace Rialto\Shopify\Order;

use Gumstix\GeographyBundle\Model\Country;
use Gumstix\GeographyBundle\Model\PostalAddress;
use JMS\Serializer\Annotation\Type;

/**
 *
 */
class Address implements PostalAddress
{
    /** @Type("integer") */
    public $id;

    /** @Type("string") */
    public $company;

    /** @Type("string") */
    public $first_name;

    /** @Type("string") */
    public $last_name;

    /** @Type("string") */
    public $name;

    /** @Type("string") */
    public $phone;

    /** @Type("string") */
    public $address1;

    /** @Type("string") */
    public $address2;

    /** @Type("string") */
    public $city;

    /** @Type("string") */
    public $country_code;

    /** @Type("string") */
    public $province;

    /** @Type("string") */
    public $province_code;

    /** @Type("string") */
    public $zip;

    public function getCity(): string
    {
        return (string) $this->city;
    }

    public function getCountryCode(): string
    {
        return (string) $this->country_code;
    }

    public function getCountryName(): string
    {
        return $this->getCountry()
            ? $this->getCountry()->getName()
            : $this->getCountryCode();
    }

    private function getCountry()
    {
        return Country::fromString($this->country_code);
    }

    public function getMailStop(): string
    {
        return '';
    }

    public function getPostalCode(): string
    {
        return (string) $this->zip;
    }

    public function getStateCode(): string
    {
        return (string) $this->province_code;
    }

    public function getStateName(): string
    {
        return (string) $this->province;
    }

    public function getStreet1(): string
    {
        return (string) $this->address1;
    }

    public function getStreet2(): string
    {
        return (string) $this->address2;
    }

    public function getCompanyName()
    {
        return (string) ($this->company ?: $this->name);
    }
}
