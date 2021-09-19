<?php

namespace Rialto\Manufacturing\Requirement\Orm;


use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Manufacturing\Requirement\MissingStockRequirement;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Item;
use Rialto\Stock\Item\PhysicalStockItem;

class MissingStockRequirementRepository extends RialtoRepositoryAbstract
{
    /** @return MissingStockRequirement */
    public function findOrCreate(Supplier $supplier, PhysicalStockItem $item)
    {
        $requirement = $this->findOneBy([
            'supplier' => $supplier,
            'stockItem' => $item,
        ]);
        if (! $requirement ) {
            $requirement = new MissingStockRequirement($supplier, $item);
            $this->_em->persist($requirement);
        }
        return $requirement;
    }

    /** @return MissingStockRequirement|object|null */
    public function findExisting(Supplier $supplier, Item $item)
    {
        return $this->findOneBy([
            'supplier' => $supplier,
            'stockItem' => $item->getSku(),
        ]);
    }
}
