<?php

namespace Rialto\Sales\Returns\Orm;

use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Sales\Returns\SalesReturn;

class SalesReturnItemRepository extends RialtoRepositoryAbstract
{
    public function findBySalesReturn(SalesReturn $rma)
    {
        return $this->findBy([
            'salesReturn' => $rma->getId()
        ]);
    }
}
