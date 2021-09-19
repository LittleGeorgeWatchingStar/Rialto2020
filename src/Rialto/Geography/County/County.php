<?php

namespace Rialto\Geography\County;

use Rialto\Entity\RialtoEntity;

/**
 * A county in the United States
 */
class County implements RialtoEntity
{
    public static function create($postalCode, $countyName)
    {
        $model = new self();
        $model->postalCode = $postalCode;
        $model->name = $countyName;
        return $model;
    }

    private $postalCode;
    private $name;
    private $Fetched;

    public function getId()
    {
        return $this->postalCode;
    }

    public function getPostalCode()
    {
        return $this->postalCode;
    }

    public function getName()
    {
        return $this->name;
    }
}
