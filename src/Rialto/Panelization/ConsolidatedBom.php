<?php

namespace Rialto\Panelization;

use Rialto\Manufacturing\Component\Component;

/**
 * The bill of materials (BOM) for all boards in a Panel.
 */
class ConsolidatedBom
{
    /** @var ConsolidatedBomItem[] */
    private $components;

    public function __construct()
    {
        $this->components = [];
    }

    /**
     * @param Component $c
     * @param string $boardId
     */
    public function add(Component $c, $boardId)
    {
        $key = $c->getFullSku();
        if (! isset($this->components[$key])) {
            $this->components[$key] = new ConsolidatedBomItem($c);
        }
        $this->components[$key]->increment($c, $boardId);
    }

    /** @return Component[] */
    public function getItems()
    {
        ksort($this->components); // sort by full SKU
        return $this->components;
    }

}
