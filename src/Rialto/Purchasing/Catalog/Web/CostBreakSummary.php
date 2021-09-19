<?php

namespace Rialto\Purchasing\Catalog\Web;

use Rialto\Purchasing\Catalog\CostBreakInterface;
use Rialto\Web\Serializer\ListableFacade;

class CostBreakSummary
{
    use ListableFacade;

    /** @var CostBreakInterface */
    private $break;

    public function __construct(CostBreakInterface $break)
    {
        $this->break = $break;
    }

    public function getUnitCost()
    {
        return $this->break->getUnitCost();
    }

    public function getMinimumOrderQty()
    {
        return $this->break->getMinimumOrderQty();
    }
}
