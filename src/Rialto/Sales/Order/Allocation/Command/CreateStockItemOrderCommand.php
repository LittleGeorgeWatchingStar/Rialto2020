<?php


namespace Rialto\Sales\Order\Allocation\Command;


use Rialto\Port\CommandBus\Command;
use Rialto\Stock\Item\Version\Version;

final class CreateStockItemOrderCommand implements Command
{
    /** @var String */
    private $itemStockCode;

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

    /** @var string */
    private $pdId;

    /** @var string */
    private $requirementId;

    public function __construct(string $stockCode, Version $version, int $orderQty, string $pdId = null, string $requirementId = null)
    {
        $this->itemStockCode = $stockCode;
        $this->version = $version;
        $this->orderQty = $orderQty;
        $this->pdId = $pdId;
        $this->requirementId = $requirementId;
    }

    public function getStockItemStockCode()
    {
        return $this->itemStockCode;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getOrderQty()
    {
        return $this->orderQty;
    }

    public function getPurchasingDataId()
    {
        return $this->pdId;
    }

    public function getRequirementId()
    {
        return $this->requirementId;
    }
}
