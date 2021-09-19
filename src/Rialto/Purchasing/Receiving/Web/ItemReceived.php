<?php

namespace Rialto\Purchasing\Receiving\Web;

use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Purchasing\Receiving\GoodsReceivedNotice;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Form model for creating a GoodsReceivedItem.
 */
abstract class ItemReceived
{
    /** @var StockProducer */
    public $poItem;

    public static function create(StockProducer $poItem, int $numOfBins = null)
    {
        if ($poItem->isAutoReceive()) {
            return new AutoReceived($poItem);
        } elseif ($poItem->isWorkOrder()) {
            return new WorkOrderReceived($poItem);
        } elseif ($poItem->isStockItem()) {
            return new StockReceived($poItem, $numOfBins);
        } else {
            return new ServiceReceived($poItem);
        }
    }

    protected function __construct(StockProducer $poItem)
    {
        $this->poItem = $poItem;
    }

    public function getDefaultBinStyle()
    {
        return $this->poItem->getBinStyle();
    }

    /**
     * @Assert\Callback
     */
    public function validateQuantity(ExecutionContextInterface $context)
    {
        if ($this->getTotalReceived() > $this->poItem->getQtyRemaining()) {
            $context->addViolation(
                "Only _rem units of _item are left to receive.", [
                '_rem' => number_format($this->poItem->getQtyRemaining()),
                '_item' => $this->poItem,
            ]);
        }
    }

    public abstract function getTotalReceived();

    public function addToGrn(GoodsReceivedNotice $grn)
    {
        $grn->addItem($this->poItem, $this->getTotalReceived());
    }

    public function isParent()
    {
        return $this->poItem->isWorkOrder() && $this->poItem->hasChild();
    }

    public function isChild()
    {
        return $this->poItem->isWorkOrder() && $this->poItem->hasParent();
    }
}
