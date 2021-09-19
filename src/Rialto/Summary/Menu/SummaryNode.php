<?php

namespace Rialto\Summary\Menu;

/**
 * A summary menu item that contains sub-items.
 */
interface SummaryNode extends Summary
{
    /**
     * @return Summary[]
     */
    public function getChildren(): array;
}
