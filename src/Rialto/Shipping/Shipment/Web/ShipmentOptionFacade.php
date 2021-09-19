<?php

namespace Rialto\Shipping\Shipment\Web;

use Rialto\Shipping\Shipment\ShipmentOption;
use Rialto\Web\Serializer\ListableFacade;

class ShipmentOptionFacade
{
    use ListableFacade;

    /** @var ShipmentOption */
    private $option;

    public function __construct(ShipmentOption $option)
    {
        $this->option = $option;
    }

    public function getCode()
    {
        return $this->option->getCode();
    }

    public function getName()
    {
        return $this->option->getName();
    }

    public function getShippingCost()
    {
        return $this->option->getShippingCost();
    }

}
