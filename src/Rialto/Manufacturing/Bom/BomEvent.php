<?php

namespace Rialto\Manufacturing\Bom;

use Rialto\Stock\Item\Version\ItemVersion;
use Symfony\Component\EventDispatcher\Event;

/**
 * An event about a Bill of Materials (BOM).
 */
class BomEvent extends Event
{
    /** @var ItemVersion */
    private $parent;

    public function __construct(ItemVersion $parent)
    {
        $this->parent = $parent;
    }

    /** @return ItemVersion */
    public function getItemVersion()
    {
        return $this->parent;
    }

}
