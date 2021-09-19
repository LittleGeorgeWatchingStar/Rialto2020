<?php

namespace Rialto\Geography\County\Orm;

use Gumstix\GeographyBundle\Model\PostalAddress;
use Rialto\Database\Orm\RialtoRepositoryAbstract;

class CountyRepository extends RialtoRepositoryAbstract
{
    public function findFromAddress(PostalAddress $address)
    {
        return $this->find($address->getPostalCode());
    }
}
