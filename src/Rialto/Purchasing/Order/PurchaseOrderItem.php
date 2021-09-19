<?php

namespace Rialto\Purchasing\Order;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Stock\Item\PurchasedStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * A line item in a purchase order.
 */
class PurchaseOrderItem extends StockProducer
{
    public static function fromPurchasingData(PurchasingData $purchData,
                                              PurchaseOrder $po): self
    {
        $poItem = new self($po);
        $poItem->initializePurchasingData($purchData);
        $poItem->setVersion($purchData->getSpecifiedVersion());
        $poItem->setDescription($purchData->getItemName());
        $requestDate = $poItem->calculateRequestedDate($purchData);
        $poItem->setRequestedDate($requestDate);
        return $poItem;
    }

    public static function fromGLAccount(GLAccount $account,
                                         PurchaseOrder $po): self
    {
        $item = new self($po);
        $item->setGLAccount($account);
        $item->setQtyOrdered(1);
        return $item;
    }

    public function __toString()
    {
        return sprintf('%s on %s',
            $this->isStockItem() ? $this->getSku() : $this->description,
            $this->purchaseOrder);
    }

    protected function validateStockItem(StockItem $item)
    {
        assertion($item instanceof PurchasedStockItem);
    }

    /**
     *
     * Given the purchasingDate return auto generated requested date
     * by adding today's date plus leadTime
     * @param $purchData PurchasingData
     * @return \DateTime
     */
    private function calculateRequestedDate(PurchasingData $purchData)
    {
        $leadTime =  $purchData->getLeadTime();
        $requestDate = new \DateTime();
        $requestDate->modify("+$leadTime days");
        return $requestDate;
    }

    /** @Assert\Callback */
    public function validateRequestDateExists(ExecutionContextInterface $context)
    {
        if (!($this->getRequestedDate() instanceof \DateTime)) {
            $poItemSKU = $this->getSku();
            $context->addViolation("$poItemSKU should have request date");
        }
    }

    /**
     * @deprecated use getDescription() instead
     */
    public function getItemDescription()
    {
        return $this->getDescription();
    }

    public function setDescription($desc)
    {
        $this->description = trim($desc);
        return $this;
    }

    public function getStockItem()
    {
        return parent::getStockItem();
    }

    /** @return Version */
    public function getVersion()
    {
        return new Version($this->version);
    }

    /**
     * Sets the version for stock items.
     */
    public function setVersion(Version $version)
    {
        if (! $version->isSpecified()) {
            throw new \InvalidArgumentException("version for $this must be specified");
        }
        $this->version = (string) $version;
    }

    /**
     * The version reference for non-stock items.
     * @return string
     */
    public function getVersionReference()
    {
        return $this->version;
    }

    /**
     * Set the version reference for non-stock items.
     * @param $version string
     */
    public function setVersionReference($version)
    {
        assertion(! $this->isStockItem());
        $this->version = trim($version);
    }

    public function getCustomization()
    {
        return null;
    }

    public function getFullSku()
    {
        if ($this->isStockItem()) {
            return $this->getSku() .
            $this->getVersion()->getStockCodeSuffix();
        }
        return '';
    }

    /** @deprecated */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    /**
     * @return int|null The quantity in stock at the supplier, as of the
     *  last sync.
     */
    public function getSupplierStockLevel()
    {
        return $this->purchasingData ? $this->purchasingData->getQtyAvailable() : null;
    }
}


