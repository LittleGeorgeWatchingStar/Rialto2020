<?php

namespace Rialto\Purchasing\Manufacturer\Web;


use Rialto\Purchasing\Manufacturer\Manufacturer;
use Rialto\Web\Serializer\ListableFacade;

class ManufacturerSummary
{
    use ListableFacade;

    /** @var Manufacturer */
    private $man;

    public function __construct(Manufacturer $man)
    {
        $this->man = $man;
    }

    public function getId(): string
    {
        return $this->man->getId();
    }

    public function getName(): string
    {
        return $this->man->getName();
    }

}
