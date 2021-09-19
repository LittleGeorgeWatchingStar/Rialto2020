<?php

namespace Rialto\Stock\Bin;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Holds the information necessary to split a stock bin into two separate
 * bins.
 */
class StockBinSplit
{
    /** @var StockBin */
    private $oldBin;

    /**
     * @var integer
     *  The quantity that will be moved to the new bin.
     *
     * @Assert\Type(type="integer")
     * @Assert\Range(min=1, minMessage="Quantity must be at least {{ limit }}.")
     */
    private $qtyToSplit = 0;

    private $printLabels = true;

    public function __construct(StockBin $bin)
    {
        $this->oldBin = $bin;
    }

    /** @return StockBin */
    public function getOldBin()
    {
        return $this->oldBin;
    }

    public function getQtyToSplit()
    {
        return $this->qtyToSplit;
    }

    /**
     * @param integer $qty
     *  The quantity that will be moved to the new bin.
     */
    public function setQtyToSplit($qty)
    {
        $this->qtyToSplit = $qty;
    }

    /**
     * @todo Never checked -- should this option even exist?
     */
    public function isPrintLabels()
    {
        return $this->printLabels;
    }

    public function setPrintLabels($print)
    {
        $this->printLabels = $print;
    }

    /** @Assert\Callback */
    public function assertQuantityValid(ExecutionContextInterface $context)
    {
        if ($this->qtyToSplit >= $this->oldBin->getQtyRemaining()) {
            $context->buildViolation(
                "Quantity must be less than amount on original bin (_qty).",
                [
                    '_qty' => number_format($this->oldBin->getQtyRemaining()),
                ])
                ->atPath('qtyToSplit')
                ->addViolation();
        }
    }
}
