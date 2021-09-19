<?php


namespace Rialto\Purchasing\Catalog\Command;


use Rialto\Port\CommandBus\Command;

final class RefreshPurchasingDataStockLevelCommand implements Command
{
    /** @var string */
    private $pdid;

    /** @var bool */
    private $updateStockLevelOnly;

    public function __construct(string $purchasingDataId, bool $updateStockLevelOnly = true)
    {
        $this->pdid = $purchasingDataId;
        $this->updateStockLevelOnly = $updateStockLevelOnly;
    }

    public function getPurchasingDataId(): string
    {
        return $this->pdid;
    }

    public function setUpdateStockLevelOnly($updateStockLevelOnly)
    {
        $this->updateStockLevelOnly = $updateStockLevelOnly;
    }

    public function getUpdateStockLevelOnly(): bool
    {
        return $this->updateStockLevelOnly;
    }
}
