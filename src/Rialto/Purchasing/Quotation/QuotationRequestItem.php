<?php

namespace Rialto\Purchasing\Quotation;

use Rialto\Entity\RialtoEntity;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Stock\Item\PhysicalStockItem;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\VersionedItem;

/**
 * QuotationRequestItem
 */
class QuotationRequestItem implements VersionedItem, RialtoEntity
{
    /**
     * @var integer
     */
    private $id;

    /** @var QuotationRequest */
    private $quotationRequest;

    /**
     * @var PhysicalStockItem
     */
    private $stockItem;

    /**
     * @var string
     */
    private $version = Version::ANY;

    /**
     * @var Customization
     */
    private $customization = null;

    /**
     * @var int[]
     * @Assert\All({
     *   @Assert\Type(type="integer", message="Quantities must be integers.")
     * })
     */
    private $quantities = [];

    /**
     * @var int[]
     * @Assert\All({
     *   @Assert\Type(type="integer", message="Lead times must be integers.")
     * })
     */
    private $leadTimes = [];

    /**
     * @var PurchasingData
     */
    private $purchasingData = null;


    public function __construct(QuotationRequest $request, PhysicalStockItem $stockItem)
    {
        $this->quotationRequest = $request;
        $this->stockItem = $stockItem;
    }

    /**
     * @return QuotationRequest
     */
    public function getQuotationRequest()
    {
        return $this->quotationRequest;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return PhysicalStockItem
     */
    public function getStockItem()
    {
        return $this->stockItem;
    }

    public function getSku()
    {
        return $this->stockItem->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function setVersion(Version $version)
    {
        $this->version = trim($version);
    }

    /**
     * @return Version
     */
    public function getVersion()
    {
        return new Version($this->version);
    }

    public function setCustomization(Customization $customization = null)
    {
        $this->customization = $customization;
    }

    /**
     * @return Customization
     */
    public function getCustomization()
    {
        return $this->customization;
    }

    public function getFullSku()
    {
        return $this->getSku()
        . $this->getVersion()->getStockCodeSuffix()
        . Customization::getStockCodeSuffix($this->customization);
    }

    /** @deprecated */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    /**
     * @return int[]
     */
    public function getQuantities()
    {
        return array_map('intval', $this->quantities);
    }

    /**
     * @param int[] $quantities
     */
    public function setQuantities(array $quantities)
    {
        $this->quantities = array_map('intval', $quantities);
    }

    /**
     * @return int[]
     */
    public function getLeadTimes()
    {
        return array_map('intval', $this->leadTimes);
    }

    /**
     * @param int[] $leadTimes
     */
    public function setLeadTimes(array $leadTimes)
    {
        $this->leadTimes = array_map('intval', $leadTimes);
    }



    public function setPurchasingData(PurchasingData $purchasingData)
    {
        $this->purchasingData = $purchasingData;
    }

    /**
     * @return PurchasingData|null
     */
    public function getPurchasingData()
    {
        return $this->purchasingData;
    }
}

