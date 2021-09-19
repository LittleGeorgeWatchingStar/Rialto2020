<?php

namespace Rialto\Allocation;

use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Stock\Facility\Facility;

interface AllocationInterface
{
    /** @return int|float */
    public function getQtyAllocated();

    public function getSource(): BasicStockSource;

    public function isWhereNeeded(): bool;

    public function getLocationWhereNeeded(): Facility;
}
