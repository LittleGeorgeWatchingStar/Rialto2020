<?php

namespace Rialto\Geppetto\Module;

use Rialto\Manufacturing\Bom\BomItem;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item;
use Rialto\Stock\Item\ManufacturedStockItem;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Item\Version\VersionIsRequired;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Represents a module used in a design and the quantity used.
 *
 * @Assert\GroupSequence({"Item", "Module"})
 */
class Module implements Item
{
    /**
     * @var StockItem
     * @Assert\NotNull(message="stockItem is required.", groups={"Item"})
     */
    private $stockItem;

    /**
     * @var Version
     * @Assert\NotNull(message="version is required.")
     * @VersionIsRequired
     */
    private $version;

    /**
     * @var integer
     * @Assert\Type(type="integer")
     * @Assert\Range(min=1, minMessage="Quantity must be positive.")
     */
    private $quantity;

    public function getStockItem()
    {
        return $this->stockItem;
    }

    public function setStockItem(StockItem $stockItem)
    {
        $this->stockItem = $stockItem;
    }

    public function getSku()
    {
        return $this->stockItem ? $this->stockItem->getSku() : 0;
    }

    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function setVersion(Version $version)
    {
        $this->version = $version;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($quantity)
    {
        $this->quantity = (int) $quantity;
    }

    /** @Assert\Callback */
    public function assertItemIsModule(ExecutionContextInterface $context)
    {
        $item = $this->stockItem;
        if (! $item->isCategory(StockCategory::MODULE)) {
            $context->buildViolation("$item is not a module.")
                ->atPath('stockItem')
                ->addViolation();
        }
    }

    /** @Assert\Callback */
    public function assertItemIsManufactured(ExecutionContextInterface $context)
    {
        $item = $this->stockItem;
        if (! $item->isManufactured()) {
            $context->buildViolation("$item is not manufactured.")
                ->atPath('stockItem')
                ->addViolation();
        }
    }

    /** @Assert\Callback */
    public function assertVersionIsValid(ExecutionContextInterface $context)
    {
        if (! $this->stockItem->hasVersion($this->version)) {
            $context->buildViolation("{$this->stockItem} has no such version {$this->version}")
                ->atPath('version')
                ->addViolation();
        }
    }

    /**
     * Adds the components of this module to the given ItemVersion.
     */
    public function addComponentsToBom(ItemVersion $parent)
    {
        assertion($this->stockItem instanceof ManufacturedStockItem);

        foreach ($this->stockItem->getBom($this->version) as $moduleComponent) {
            /* @var $moduleComponent BomItem */
            $boardComponent = $parent->addComponent(
                $moduleComponent->getComponent(),
                $moduleComponent->getQuantity() * $this->quantity);
            $boardComponent->setWorkType($moduleComponent->getWorkType());
            $boardComponent->setVersion($moduleComponent->getVersion());
        }
    }
}


