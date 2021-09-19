<?php

namespace Rialto\PcbNg\Command;


use Rialto\Port\CommandBus\Command;

class CreateManufacturedStockItemPcbNgPurchasingDataCommand implements Command
{
    /** @var string */
    private $manufacturedStockItemSku;

    /** @var string */
    private $version;

    public function __construct(string $manufacturedStockItemSku,
                                string $version)
    {
        $this->manufacturedStockItemSku = $manufacturedStockItemSku;
        $this->version = $version;
    }

    public function getManufacturedStockItemSku(): string
    {
        return $this->manufacturedStockItemSku;
    }

    public function getVersion(): string
    {
        return $this->version;
    }
}