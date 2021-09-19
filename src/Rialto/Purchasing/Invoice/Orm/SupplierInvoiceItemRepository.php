<?php

namespace Rialto\Purchasing\Invoice\Orm;

use Rialto\Database\Orm\RialtoRepositoryAbstract;
use Rialto\Purchasing\Supplier\Supplier;

class SupplierInvoiceItemRepository extends RialtoRepositoryAbstract
{
    public function findBySupplierReference(Supplier $supp, $ref)
    {
        return $this->findBy([
            'supplier' => $supp->getId(),
            'supplierReference' => $ref
        ]);
    }
}
