<?php


namespace Rialto\Ciiva\ApiDto;


final class GetSupplierComponentsByPartNumberForAltiumResponse
{
    /** @var ComponentPair[] */
    private $componentPairs;

    /**
     * @param ComponentPair[] $componentPairs
     */
    public function __construct(array $componentPairs)
    {
        $this->componentPairs = $componentPairs;
    }

    /**
     * @return ComponentPair[]
     */
    public function getComponentPairs(): array
    {
        return $this->componentPairs;
    }
}

final class ComponentPair
{
    /** @var SupplierComponent */
    private $supplierComponent;

    /** @var ManufacturerComponent */
    private $manufacturerComponent;

    public function __construct(SupplierComponent $supplierComponent,
                                ManufacturerComponent $manufacturerComponent)
    {
        $this->supplierComponent = $supplierComponent;
        $this->manufacturerComponent = $manufacturerComponent;
    }

    public function getSupplierComponent(): SupplierComponent
    {
        return $this->supplierComponent;
    }

    public function getManufacturerComponent(): ManufacturerComponent
    {
        return $this->manufacturerComponent;
    }
}
