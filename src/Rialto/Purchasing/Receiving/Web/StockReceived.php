<?php

namespace Rialto\Purchasing\Receiving\Web;

use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Purchasing\Receiving\GoodsReceivedNotice;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Subclass of ItemReceived for receiving actual physical stock.
 */
class StockReceived extends ItemReceived
{
    const DEFAULT_MAX_BINS = 10;

    /**
     * @var BinReceived[]
     * @Assert\Valid(traverse=true)
     */
    public $bins = [];

    public function __construct(StockProducer $poItem, int $numOfBins = null)
    {
        parent::__construct($poItem);

        if ($numOfBins !== null) {
            $binsToCreate = min($this::DEFAULT_MAX_BINS, $numOfBins);
            for ($x = 0; $x < $binsToCreate; $x++) {
                $this->bins[] = new BinReceived($poItem->getBinStyle(),
                    $poItem->getQtyRemaining() / $numOfBins);
            }
        } else {
            $this->bins[] = new BinReceived($poItem->getBinStyle(),
                $poItem->getQtyRemaining());
        }
    }

    public function getTotalReceived()
    {
        $total = 0;
        foreach ($this->bins as $bin) {
            $total += $bin->qtyReceived;
        }
        return $total;
    }

    public function addToGrn(GoodsReceivedNotice $grn)
    {
        foreach ($this->bins as $bin) {
            if ($bin->qtyReceived <= 0) {
                continue;
            }
            $grnItem = $grn->addItem($this->poItem, $bin->qtyReceived);
            $grnItem->setBinStyle($bin->binStyle);
        }
    }
}
