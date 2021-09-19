<?php

namespace Rialto\Shopify\Order;

use JMS\Serializer\Annotation\Type;

/**
 *
 */
class Customer
{
    /**
     * @var Address
     * @Type("Rialto\Shopify\Order\Address")
     */
    public $default_address;

    /** @Type("string") */
    public $email;

    /** @Type("string") */
    public $first_name;

    /** @Type("string") */
    public $id;

    /** @Type("string") */
    public $last_name;

    public function getFullName()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getCompanyName()
    {
        return $this->default_address->company ?: $this->getFullName();
    }
}
