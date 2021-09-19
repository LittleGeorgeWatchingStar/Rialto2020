<?php

namespace Rialto\Stock\Item\Version;

use Rialto\Database\Orm\DbManager;
use Rialto\Measurement\Dimensions;
use Rialto\Stock\ChangeNotice\ChangeNotice;
use Rialto\Stock\ChangeNotice\Web\ChangeNoticeList;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * The ItemVersion creation form binds to this class to create new
 * ItemVersion instances.
 */
class ItemVersionTemplate
{
    /** @var StockItem */
    private $stockItem;

    /**
     * @var string
     * @Assert\NotBlank(message="Version cannot be blank.",
     *   groups={"versioned"})
     * @Assert\Blank(message="Version must be blank.",
     *   groups={"unversioned"})
     * @Assert\Length(max=31,
     *   maxMessage="Version cannot be more than {{ limit }} characters long.")
     */
    private $versionCode;

    /**
     * @Assert\Type(type="numeric", message="Weight must be a number.")
     * @Assert\NotBlank(message="Weight cannot be blank.",
     *   groups={"dimensions"})
     * @Assert\Range(min=0.0001,
     *   minMessage="Weight must be at least {{ limit }} kg.",
     *   groups={"dimensions"})
     */
    private $weight;

    /**
     * @var Dimensions
     * @Assert\NotNull(message="Dimensions are required.",
     *   groups={"dimensions"})
     * @Assert\Valid
     */
    private $dimensions;

    private $autoBuildVersion = false;

    private $shippingVersion = false;

    /** @var ChangeNoticeList */
    private $changeList = null;

    public function setStockItem(StockItem $stockItem)
    {
        $this->stockItem = $stockItem;
    }

    public function loadDefaultValues()
    {
        if (! $this->stockItem) {
            return;
        }
        $version = $this->stockItem->getAutoBuildVersion();
        $this->weight = $version->getWeight();
        $this->dimensions = $version->getDimensions();
    }

    public function getVersionCode()
    {
        return $this->versionCode;
    }

    public function setVersionCode($versionCode)
    {
        $this->versionCode = $versionCode;
    }

    public function getWeight()
    {
        return $this->weight;
    }

    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    public function getDimensions()
    {
        return $this->dimensions;
    }

    public function setDimensions(Dimensions $dim)
    {
        $this->dimensions = $dim;
    }

    public function isAutoBuildVersion()
    {
        return $this->autoBuildVersion;
    }

    public function setAutoBuildVersion($bool)
    {
        $this->autoBuildVersion = $bool;
    }

    public function isShippingVersion()
    {
        return $this->shippingVersion;
    }

    public function setShippingVersion($bool)
    {
        $this->shippingVersion = $bool;
    }

    public function getChangeList()
    {
        return $this->changeList;
    }

    public function setChangeList(ChangeNoticeList $notices)
    {
        $this->changeList = $notices;
    }

    /** @return ItemVersion */
    public function create()
    {
        $version = $this->stockItem->addVersion($this->versionCode);
        $version->setWeight($this->weight);
        $version->setDimensions($this->dimensions);
        if ($this->autoBuildVersion) {
            $this->stockItem->setAutoBuildVersion($version);
        }
        if ($this->shippingVersion) {
            $this->stockItem->setShippingVersion($version);
        }
        return $version;
    }

    /** @Assert\Callback */
    public function validateDuplicateVersion(ExecutionContextInterface $context)
    {
        $version = $this->versionCode;
        if ($this->stockItem && $this->stockItem->hasVersion($version)) {
            $item = $this->stockItem;
            $context->buildViolation(
                "$item already has a version that matches '$version'.")
                ->atPath('versionCode')
                ->addViolation();
        }
    }

    /**
     * Updates any selected notices to include $version and creates a
     * new notice if requested.
     *
     * @return ChangeNotice[]
     */
    public function getNotices(ItemVersion $version)
    {
        return $this->changeList->getNotices($version);
    }

    public function persistNotices(array $notices, DbManager $dbm, EventDispatcherInterface $dispatcher)
    {
        $this->changeList->persistNotices($notices, $dbm, $dispatcher);
    }
}
