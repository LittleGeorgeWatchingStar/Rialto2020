<?php

namespace Rialto\Purchasing\Order;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Stock\Item;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Creates a purchase order for a single item.
 */
class SingleItemPurchaseOrder implements PurchaseInitiator, Item
{
    const INITIATOR_CODE = 'SIPO';

    /**
     * @var StockItem
     * @Assert\NotNull(groups={"Default", "purchasing"})
     * @Assert\Valid
     */
    private $item;

    /**
     * @var Version
     * @Assert\NotNull
     */
    private $version;

    /**
     * @var integer
     * @Assert\Type(
     *   type="numeric",
     *   message="Order quantity must be a number.",
     *   groups={"Default", "purchasing"})
     * @Assert\Range(
     *   min=1,
     *   minMessage="Order quantity must be at least {{ limit }}.",
     *   groups={"Default", "purchasing"})
     */
    private $orderQty = null;

    private $date;

    /**
     * @var PurchasingData
     * @Assert\NotNull(
     *   message="No purchasing data record matches the constraints.",
     *   groups="purchData")
     */
    private $purchData = null;

    public function __construct(StockItem $item)
    {
        $this->item = $item;
        $this->orderQty = $item->getEconomicOrderQty();
        $this->version = $item->getAutoBuildVersion();
    }

    public function getStockItem()
    {
        return $this->item;
    }

    public function getSku()
    {
        return $this->item->getSku();
    }

    /** @deprecated use getSku() instead */
    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function isVersioned()
    {
        return $this->item->isVersioned();
    }

    /** @return Version */
    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion(Version $version)
    {
        assertion($version->isSpecified());
        $this->version = $version;
    }

    public function getOrderQty()
    {
        return $this->orderQty;
    }

    public function setOrderQty($orderQty)
    {
        $this->orderQty = $orderQty;
    }

    public function getRequestedDate()
    {
        return $this->date;
    }

    public function setRequestedDate(DateTime $date = null)
    {
        $this->date = $date;
    }

    public function loadPurchasingData(ObjectManager $om)
    {
        /** @var $repo PurchasingDataRepository */
        $repo = $om->getRepository(PurchasingData::class);
        $this->purchData = $repo->findPreferredForSingleItemPO($this);
    }

    /** @return PurchasingData */
    public function getPurchasingData()
    {
        return $this->purchData;
    }

    public function getInitiatorCode()
    {
        return self::INITIATOR_CODE;
    }
}
