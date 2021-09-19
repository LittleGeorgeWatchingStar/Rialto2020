<?php

namespace Rialto\Allocation\Source;

/**
 * A stock source that is composed of other stock sources.
 *
 * For example, a StockLevel for a controlled item is really just a collection
 * of StockBins.
 */
interface CompoundStockSource extends StockSource
{
    /**
     * Returns the stock sources that comprise this compound source.
     *
     * @return BasicStockSource[]
     */
    public function getComponentSources();
}
