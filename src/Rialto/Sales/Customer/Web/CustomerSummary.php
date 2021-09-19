<?php

namespace Rialto\Sales\Customer\Web;


use Rialto\Sales\Customer\Customer;
use Rialto\Web\Serializer\ListableFacade;

class CustomerSummary
{
    use ListableFacade;

    /** @var Customer */
    private $c;

    public function __construct(Customer $c)
    {
        $this->c = $c;
    }

    public function getId()
    {
        return $this->c->getId();
    }

    public function getName()
    {
        return $this->c->getName();
    }

    public function getCompanyName()
    {
        return $this->c->getCompanyName();
    }

    public function getEmail()
    {
        return $this->c->getEmail();
    }
}
