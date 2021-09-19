<?php

namespace Rialto\Panelization;

use Rialto\Manufacturing\Customization\Customization;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Quotation\QuotationRequest;
use Rialto\Purchasing\Quotation\QuotationRequestItem;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\VersionedItem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A board that will be added to a panelized PO, but has not been positioned
 * on the panel yet.
 */
class UnplacedBoardWithQty implements VersionedItem
{
    /**
     * @var ItemVersion
     * @Assert\Valid
     */
    private $itemVersion;

    /** @var PurchasingData */
    private $purchasingData = null;

    /**
     * @var int
     * @Assert\Range(min=1, max=100000)
     */
    private $boardsPerPanel;

    /**
     * @return int
     */
    public function getBoardsPerPanel()
    {
        return $this->boardsPerPanel;
    }

    /**
     * @param int $boardsPerPanel
     */
    public function setBoardsPerPanel($boardsPerPanel)
    {
        $this->boardsPerPanel = $boardsPerPanel;
    }

    /**
     * @return ItemVersion
     */
    public function getItemVersion()
    {
        return $this->itemVersion;
    }

    /**
     * @param ItemVersion $itemVersion
     */
    public function setItemVersion($itemVersion)
    {
        $this->itemVersion = $itemVersion;
    }

    /** @return Customization|null */
    public function getCustomization()
    {
        return null;
    }

    public function getSku()
    {
        return $this->itemVersion->getSku();
    }


    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /** @return Version */
    public function getVersion()
    {
        return $this->itemVersion->getVersion();
    }

    /** @return StockItem */
    public function getStockItem()
    {
        return $this->itemVersion->getStockItem();
    }

    /** @deprecated */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    public function getFullSku()
    {
        return $this->itemVersion->getFullSku();
    }

    public function __toString()
    {
        return $this->getFullSku();
    }

    public function getPurchasingData(): PurchasingData
    {
        return $this->purchasingData;
    }

    public function setPurchasingData(PurchasingData $purchasingData)
    {
        $this->purchasingData = $purchasingData;
    }

    /**
     * @Assert\Callback
     */
    public function assertBomHasPcb(ExecutionContextInterface $context)
    {
        if (null === $this->getPcbVersion()) {
            $context->buildViolation('panelization.no_pcb')
                ->setParameter('{{ sku }}', $this->getFullSku())
                ->addViolation();
        }
    }

    /**
     * @return null|ItemVersion
     */
    private function getPcbVersion()
    {
        $bomItems = $this->itemVersion->getBomItems();
        foreach ($bomItems as $bomItem) {
            if ($bomItem->isCategory(StockCategory::PCB)) {
                return $bomItem->getAutoBuildVersion();
            }
        }
        return null;
    }

    /**
     * @Assert\Callback
     */
    public function assertPcbHasDimensions(ExecutionContextInterface $context)
    {
        $pcb = $this->getPcbVersion();
        if (!$pcb) {
            return;
        }
        if (!$pcb->hasDimensions()) {
            $context->buildViolation('panelization.no_dimensions')
                ->setParameter('{{ sku }}', $pcb->getFullSku())
                ->addViolation();
        }
    }

    public function createQuotationRequestItem(QuotationRequest $rfq): QuotationRequestItem
    {
        $pcb = $this->getPcbVersion();
        assert(null !== $pcb);
        $rItem = $rfq->createItem($pcb->getStockItem());
        $rItem->setVersion($pcb->getVersion());
        $rItem->setCustomization($pcb->getCustomization());
        return $rItem;
    }
}
