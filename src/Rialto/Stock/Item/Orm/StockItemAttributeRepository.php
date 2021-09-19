<?php

namespace Rialto\Stock\Item\Orm;

use Rialto\Entity\EntityAttribute;
use Rialto\Entity\Orm\EntityAttributeRepository;

class StockItemAttributeRepository extends EntityAttributeRepository
{
    /**
     * @return EntityAttribute[]
     */
    public function findByEntity($stockItem)
    {
        return $this->findBy(['stockItem' => $stockItem]);
    }

}
