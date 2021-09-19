<?php

namespace Rialto\Sales\Customer;

use Rialto\Entity\RialtoEntity;

class SalesArea implements RialtoEntity
{
    const WORLDWIDE = 'XX';

    private $id;
    private $description = '';

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function __toString()
    {
        return $this->description;
    }

    public function getId()
    {
        return $this->id;
    }
}


