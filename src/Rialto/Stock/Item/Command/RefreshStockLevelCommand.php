<?php


namespace Rialto\Stock\Item\Command;


use Rialto\Port\CommandBus\Command;

final class RefreshStockLevelCommand implements Command
{
    /** @var string */
    private $sku;

    public function __construct(string $itemSku)
    {
        $this->sku = $itemSku;
    }

    public function getItemSku(): string
    {
        return $this->sku;
    }
}
