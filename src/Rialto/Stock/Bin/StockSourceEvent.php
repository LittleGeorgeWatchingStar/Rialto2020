<?php

namespace Rialto\Stock\Bin;

use Rialto\Allocation\Source\BasicStockSource;
use Symfony\Component\EventDispatcher\Event;

/**
 * An event that involves a stock source.
 *
 * @see BasicStockSource
 */
class StockSourceEvent extends Event
{
    private $source;

    public function __construct(BasicStockSource $source)
    {
        $this->source = $source;
    }

    /** @return BasicStockSource */
    public function getSource()
    {
        return $this->source;
    }
}
