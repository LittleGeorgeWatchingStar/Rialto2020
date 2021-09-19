<?php

namespace Rialto\Purchasing\Supplier\Web;

use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Web\Serializer\ListableFacade;

class SupplierSummary
{
    use ListableFacade;

    /** @var Supplier */
    private $supplier;

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function getId()
    {
        return $this->supplier->getId();
    }

    public function getName()
    {
        return $this->supplier->getName();
    }

    public function getWebsite()
    {
        return $this->supplier->getWebsite();
    }
}
