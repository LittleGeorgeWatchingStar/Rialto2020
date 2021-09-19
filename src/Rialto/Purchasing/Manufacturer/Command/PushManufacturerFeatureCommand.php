<?php


namespace Rialto\Purchasing\Manufacturer\Command;


use Rialto\Stock\Item\StockItem;

/**
 * Command payload representing a request pushing module manufacturer information
 * to Madison.
 */
final class PushManufacturerFeatureCommand
{
    /** @var int[] */
    private $moduleIds;

    /**
     * @param int[] $moduleIds
     */
    public function __construct(array $moduleIds)
    {
        $this->moduleIds = $moduleIds;
    }

    /**
     * @param StockItem[] $modules
     * @return PushManufacturerFeatureCommand
     */
    public static function fromModules(array $modules): self
    {
        $ids = array_map(function (StockItem $item) {
            return $item->getId();
        }, $modules);

        return new self($ids);
    }

    /**
     * @return int[]
     */
    public function getModuleIds(): array
    {
        return $this->moduleIds;
    }
}
