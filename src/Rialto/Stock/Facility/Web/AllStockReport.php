<?php

namespace Rialto\Stock\Facility\Web;

use Rialto\Stock\Facility\Facility;

class AllStockReport
{
    /** @var Facility */
    public $location = null;

    /** @var string */
    public $sellable = '';

    public function getTitle()
    {
        $suffix = $this->location ? " at {$this->location}" : '';
        return "All stock{$suffix}";
    }
}
