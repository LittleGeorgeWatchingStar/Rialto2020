<?php

namespace Rialto\Ups\Shipping\Webservice;

use Gumstix\GeographyBundle\Model\PostalAddress;
use Rialto\Web\TwigExtensionTrait;
use Twig\Extension\AbstractExtension;

/**
 * Twig extensions for the UPS bundle.
 */
class UpsExtension extends AbstractExtension
{
    use TwigExtensionTrait;

    public function getFilters()
    {
        return [
            $this->simpleFilter('upsLen', 'length', ['xml', 'html']),
            $this->simpleFilter('upsNum', 'number', ['xml', 'html']),
            $this->simpleFilter('kgsToLbs', 'kgsToLbs', ['xml', 'html']),
            $this->simpleFilter('harmonization', 'harmonization', ['xml', 'html']),
        ];
    }

    public function getFunctions()
    {
        return [
            $this->simpleFunction('upsAddress', 'address', ['xml', 'html']),
            $this->simpleFunction('upsAddressArtifactFormat', 'addressArtifactFormat', ['xml', 'html']),
        ];
    }

    public function length($string, $maxLength)
    {
        // Convert non-ASCII characters
        $string = utf8ToAscii($string);
        // Turn newlines into spaces
        $string = preg_replace('/[\n\r]/', ' ', $string);

        // Establish an upper bound on the length of the unescaped string
        $string = substr($string, 0, $maxLength);

        /* Make sure the escaped version of the string is shorter than the
         * limit. However, we can't trim the escaped version itself, because
         * we don't want to split any HTML entities (eg, "&amp;" => "&am");
         * doing so would create an invalid XML document. */
        $escaped = htmlentities($string);
        while (strlen($escaped) > $maxLength) {
            $string = substr($string, 0, strlen($string) - 1);
            $escaped = htmlentities($string);
        }
        return $escaped;
    }

    /**
     * Formats a number for use in UPS XML APIs.
     *
     * @param int $number
     * @param int $decimalPlaces
     * @return string
     */
    public function number($number, $decimalPlaces)
    {
        static $decimalPoint = '.';
        static $thousandsSep = '';
        return number_format($number, $decimalPlaces, $decimalPoint, $thousandsSep);
    }

    public function kgsToLbs($kgs)
    {
        $lbs = $kgs * 2.2;
        if ($lbs < 0.1) $lbs = 0.1;
        return $lbs;
    }

    public function harmonization($code)
    {
        return preg_replace('/\D/', '', (string) $code);
    }

    public function address(PostalAddress $address, string $customerRef = '')
    {
        $output = "
            <Address>
                <AddressLine1>_street1</AddressLine1>
                <AddressLine2>_street2</AddressLine2>
                <AddressLine3>_street3</AddressLine3>
                <City>_city</City>
                <StateProvinceCode>_stateCode</StateProvinceCode>
                <PostalCode>_postalCode</PostalCode>
                <CountryCode>_countryCode</CountryCode>
            </Address>";

        return strtr($output, [
            '_street1' => $this->length($address->getStreet1(), 35),
            '_street2' => $this->length($address->getStreet2(), 35),
            '_street3' => $this->length($customerRef, 35),
            '_city' => $this->length($address->getCity(), 30),
            '_stateCode' => $this->length($address->getStateCode(), 5),
            '_postalCode' => $this->length($address->getPostalCode(), 10),
            '_countryCode' => $address->getCountryCode(),
        ]);
    }

    public function addressArtifactFormat(PostalAddress $address)
    {
        $output = "
             <AddressArtifactFormat>
                  <PoliticalDivision2>_city</PoliticalDivision2>
                  <PoliticalDivision1>_stateCode</PoliticalDivision1>
                  <CountryCode>_countryCode</CountryCode>
                  <PostcodePrimaryLow>_postalCode</PostcodePrimaryLow>
            </AddressArtifactFormat>";

        return strtr($output, [
            '_city' => $this->length($address->getCity(), 30),
            '_stateCode' => $this->length($address->getStateCode(), 30),
            '_countryCode' => $address->getCountryCode(),
            '_postalCode' => $this->length($address->getPostalCode(), 10),
        ]);
    }
}
