<?php

namespace Rialto\Stock\Returns\Problem;

use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Returns\ReturnedItem;
use Rialto\Stock\Transfer\Transfer;
use Symfony\Component\Form\FormInterface;

/**
 * Holds information needed to resolve a returned item.
 */
class ItemResolution
{
    /** @var ReturnedItem */
    private $item;

    /** @var ItemResolverLimits */
    private $limits;

    /**
     * These bins can be adjusted to counterbalance any stock adjustment
     * made to the returned bin.
     *
     * @var StockBin[]
     */
    private $otherBins = [];

    /** @var FormInterface */
    private $form = null;

    public function __construct(ReturnedItem $item, ItemResolverLimits $limits)
    {
        $this->item = $item;
        $this->limits = $limits;
    }

    public function getItem()
    {
        return $this->item;
    }

    public function setOtherBins(array $bins)
    {
        $this->otherBins = $bins;
        foreach ($this->otherBins as $bin) {
            $bin->setNewQty($bin->getQtyRemaining());
        }
    }

    /**
     * @return StockBin[]
     */
    public function getOtherBins()
    {
        return $this->otherBins;
    }

    /**
     * @return FormInterface
     */
    public function getForm()
    {
        return $this->form;
    }

    public function setForm(FormInterface $form)
    {
        $this->form = $form;
    }

    public function hasSolutions()
    {
        return null !== $this->form;
    }

    public function getFormView()
    {
        return $this->form->createView();
    }

    /**
     * Imagine we got a bin back from location X, but Rialto thinks the bin is
     * currently in transit to X via an outstanding transfer. We can resolve
     * the item by simply completing the outstanding transfer.
     */
    public function getOpenTransferToCM()
    {
        $transfer = $this->item->getOutstandingTransfer();
        if ($transfer && $this->canBeReceived($transfer)) {
            return $transfer;
        }
        return null;
    }

    private function canBeReceived(Transfer $transfer)
    {
        return $transfer->isSent()
            && (!$transfer->isReceived())
            && $transfer->isDestinedFor($this->item->getReturnedFrom())
            && $this->limits->canBeReceived($this->item, $transfer);
    }
}
