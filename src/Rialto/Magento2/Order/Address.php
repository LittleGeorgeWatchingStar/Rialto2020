<?php

namespace Rialto\Magento2\Order;

use Gumstix\GeographyBundle\Model\Country;
use Gumstix\GeographyBundle\Model\PostalAddress;
use JMS\Serializer\Annotation\Type;

/**
 * A deserialized address from the Magento API.
 */
class Address implements PostalAddress
{
    const TYPE_BILLING = 'billing';

    const TYPE_SHIPPING = 'shipping';

    /** @Type("string") */
    public $company;

    /** @Type("string") */
    public $firstname;

    /** @Type("string") */
    public $lastname;

    /** @Type("string") */
    public $email;

    /** @Type("string") */
    public $telephone;

    /** @Type("string") */
    public $address_type;

    /** @Type("array") */
    public $street;

    /** @Type("string") */
    public $city;

    /** @Type("string") */
    public $region;

    /** @Type("string") */
    public $postcode;

    /** @Type("string") */
    public $country_id;

    /** @Type("string") */
    public $vat_id;

    public function getStreet1(): string
    {
        return (string) ($this->street[0]);
    }

    public function getStreet2(): string
    {
        return (string) ($this->street[1] ?? '');
    }

    public function getMailStop(): string
    {
        return '';
    }

    public function getCity(): string
    {
        return (string) $this->city;
    }

    public function getStateCode(): string
    {
        return (string) ($this->getCountry()->resolveStateCode($this->region)
            ?: $this->region);
    }

    public function getStateName(): string
    {
        return (string) ($this->getCountry()->resolveStateName($this->region)
            ?: $this->region);
    }

    public function getPostalCode(): string
    {
        return (string) $this->postcode;
    }

    public function getCountryCode(): string
    {
        return (string) $this->country_id;
    }

    public function getCountryName(): string
    {
        return $this->getCountry()->getName();
    }

    /** @return Country */
    private function getCountry()
    {
        return new Country($this->country_id);
    }

    public function isType($type)
    {
        return $type == $this->address_type;
    }

    public function getFullName()
    {
        return sprintf('%s %s',
            $this->firstname,
            $this->lastname);
    }
}
