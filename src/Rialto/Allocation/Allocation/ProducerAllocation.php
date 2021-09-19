<?php

namespace Rialto\Allocation\Allocation;

use Rialto\Allocation\Requirement\Requirement;
use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Purchasing\Producer\StockProducer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An allocation of stock from a StockProducer.
 *
 * @see StockProducer
 */
class ProducerAllocation extends StockAllocation
{
    /**
     * @var StockProducer
     * @Assert\NotNull
     */
    private $source;

    public function __construct(Requirement $request, StockProducer $source)
    {
        parent::__construct($request, $source->getStockItem());
        $this->source = $source;
    }

    public function getSource(): BasicStockSource
    {
        return $this->source;
    }
}

