<?php

namespace Rialto\Purchasing\Supplier\Attribute\Orm;

use Rialto\Entity\EntityAttribute;
use Rialto\Entity\Orm\EntityAttributeRepository;
use Rialto\Purchasing\Supplier\Attribute\SupplierAttribute;
use Rialto\Purchasing\Supplier\Supplier;

class SupplierAttributeRepository extends EntityAttributeRepository
{
    public function findByEntity($supplier)
    {
        return $this->findBy(['supplier' => $supplier]);
    }

    public function get(Supplier $supplier, $name)
    {
        $attribute = $this->findOneBy([
            'supplier' => $supplier->getId(),
            'attribute' => EntityAttribute::normalize($name),
        ]);
        if (! $attribute ) {
            $attribute = new SupplierAttribute();
            $attribute->setSupplier($supplier);
            $attribute->setAttribute($name);
        }
        return $attribute;
    }

}
