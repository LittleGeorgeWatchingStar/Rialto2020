<?php

namespace Rialto\Geography\Address;

use Gumstix\GeographyBundle\Model\Country;
use Gumstix\GeographyBundle\Model\PostalAddress;
use Gumstix\GeographyBundle\Service\AddressFormatter;
use Rialto\Database\Orm\Persistable;
use Rialto\Entity\RialtoEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A postal address.
 *
 * This class is immutable by design so that address information
 * can be preserved for record-keeping purposes.
 */
class Address implements PostalAddress, RialtoEntity, Persistable
{
    private $id;

    /**
     * @Assert\NotBlank(message="Street 1 cannot be blank.")
     * @Assert\Length(max=255)
     */
    private $street1;

    /**
     * @var string
     * @Assert\Length(max=255)
     */
    private $street2;

    /**
     * @var string
     * @Assert\Length(max=255)
     */
    private $mailStop;

    /**
     * @Assert\NotBlank(message="City cannot be blank.")
     * @Assert\Length(max=255)
     */
    private $city;

    /**
     * Preferably the code of the state or province, or failing that,
     * the name.
     *
     * @var string
     * @Assert\NotBlank(message="State cannot be blank.")
     * @Assert\Length(max=255)
     */
    private $stateCode;

    /**
     * @Assert\NotBlank(message="Postal code cannot be blank.")
     * @Assert\Length(max=50)
     */
    private $postalCode;

    /**
     * @Assert\NotBlank(message="Please select a country.")
     * @Assert\Length(max=255)
     * Assert\Country // does not seem to work for Denmark (DK)
     */
    private $countryCode;

    /**
     * Factory method.
     *
     * @param PostalAddress $original
     * @return Address
     */
    public static function fromAddress(PostalAddress $original)
    {
        if ($original instanceof self) {
            return $original;
        }

        return new self(
            $original->getStreet1(),
            $original->getStreet2(),
            $original->getMailStop(),
            $original->getCity(),
            $original->getStateCode() ?: $original->getStateName(),
            $original->getPostalCode(),
            $original->getCountryCode()
        );
    }

    /**
     * Factory method.
     *
     * @param string[] $array
     * @return Address
     */
    public static function fromArray(array $array)
    {
        return new self(
            $array['street1'],
            $array['street2'] ?? "",
            $array['mailStop'] ?? "",
            $array['city'],
            $array['stateCode'],
            $array['postalCode'],
            $array['countryCode']
        );
    }

    public function __construct(
        $street1,
        $street2,
        $mailStop,
        $city,
        $stateCode,
        $postalCode,
        $countryCode)
    {
        $this->street1 = self::prep($street1);
        $this->street2 = self::prep($street2);
        $this->mailStop = self::prep($mailStop);
        $this->city = self::prep($city);
        $this->stateCode = self::prep($stateCode);
        $this->postalCode = strtoupper(self::prep($postalCode));
        $this->countryCode = self::prep($countryCode);
    }

    /**
     * Preps a string for use in an address field.
     * @param string $string
     * @return string
     */
    public static function prep($string)
    {
        return trim($string);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getStreet1(): string
    {
        return $this->street1;
    }

    public function getStreet2(): string
    {
        return $this->street2;
    }

    public function getMailStop(): string
    {
        return $this->mailStop;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getStateName(): string
    {
        $country = $this->getCountry();
        $name = $country ?
            $country->resolveStateName($this->stateCode) : null;
        return $name ?: $this->stateCode;
    }

    public function getStateCode(): string
    {
        $country = $this->getCountry();
        $code = $country ?
            $country->resolveStateCode($this->stateCode) : null;
        return $code ?: $this->stateCode;
    }

    /** @Assert\Callback */
    public function validateStateCode(ExecutionContextInterface $context)
    {
        $country = $this->getCountry();
        if (! $country ) {
            return;
        }
        if (! $country->hasStateList() ) {
            return;
        }
        if (! $country->resolveStateCode( $this->stateCode ) ) {
            $value = $this->stateCode;
            $region = $country->getCode() == 'US' ? 'state' : 'province';
            $context->buildViolation("$value is not a valid $region in $country.")
                ->atPath('stateCode')
                ->addViolation();
        }
    }

    public function getPostalCode(): string
    {
        return $this->postalCode;
    }

    /** @Assert\Callback */
    public function validatePostalCode(ExecutionContextInterface $context)
    {
        $country = $this->getCountry();
        if (! $country ) {
            return;
        }
        $ok = true;
        switch ($country->getCode()) {
            case 'US':
                $ok = preg_match('/^\d\d\d\d\d(-?\d\d\d\d)?$/', $this->postalCode);
                break;
            case 'CA':
                $ok = preg_match('/^[A-Z]\d[A-Z]\s*\d[A-Z]\d$/', $this->postalCode);
                break;
        }
        if (! $ok ) {
            $code = $this->postalCode;
            $context->buildViolation("'$code' is not a valid postal code for $country.")
                ->atPath('postalCode')
                ->addViolation();
        }
    }

    /** @return Country */
    public function getCountry()
    {
        $code = $this->getCountryCode();
        return $code ?
            new Country($code) :
            null;
    }

    public function getCountryCode(): string
    {
        return Country::resolveCountryCode($this->countryCode);
    }

    public function getCountryName(): string
    {
        return Country::resolveCountryName($this->countryCode);
    }

    public function __toString()
    {
        $formatter = new AddressFormatter();
        return $formatter->toString($this);
    }

    public function toArray()
    {
        $formatter = new AddressFormatter();
        return $formatter->toArray($this);
    }

    public function getEntities()
    {
        return [$this];
    }
}
