<?php

namespace Rialto\Stock\Level;

use Rialto\Alert\HasWarnings;
use Symfony\Component\EventDispatcher\Event;

class StockLevelEvent extends Event
{
    use HasWarnings;

    /** @var AvailableStockLevel */
    private $level;

    public function __construct(AvailableStockLevel $level)
    {
        $this->level = $level;
    }

    /**
     * @return AvailableStockLevel
     */
    public function getLevel()
    {
        return $this->level;
    }
}
