<?php

namespace Rialto\Geography\Address\Orm;

use Gumstix\GeographyBundle\Model\PostalAddress;
use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Geography\Address\Address;

class AddressRepository extends RialtoRepositoryAbstract
{
    /**
     * Finds the first matching address or returns a new one.
     *
     * @return Address
     */
    public function findOrCreate(PostalAddress $original)
    {
        $address = $this->findOneBy([
            'street1' => Address::prep($original->getStreet1()),
            'street2' => Address::prep($original->getStreet2()),
            'mailStop' => Address::prep($original->getMailStop()),
            'city' => Address::prep($original->getCity()),
            'stateCode' => Address::prep($original->getStateCode()),
            'postalCode' => Address::prep($original->getPostalCode()),
            'countryCode' => Address::prep($original->getCountryCode()),
        ]);
        if (!$address) {
            $address = Address::fromAddress($original);
            $this->_em->persist($address);
        }
        return $address;
    }
}
