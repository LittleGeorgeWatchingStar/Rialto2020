<?php

namespace Rialto\Allocation\Allocation;

use Rialto\Allocation\Requirement\Requirement;
use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Stock\Bin\StockBin;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An allocation of stock from a StockBin.
 *
 * @see StockBin
 */
class BinAllocation extends StockAllocation
{
    /**
     * @var StockBin
     * @Assert\NotNull
     */
    private $source;

    public function __construct(Requirement $requirement, StockBin $source)
    {
        parent::__construct($requirement, $source->getStockItem());
        $this->source = $source;
    }

    public function getSource(): BasicStockSource
    {
        return $this->source;
    }

    public function __toString()
    {
        return (string)$this->getRequirement();
    }
}

