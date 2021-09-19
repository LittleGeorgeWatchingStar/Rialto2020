<?php

namespace Rialto\Madison;

use Exception;
use Rialto\Geppetto\Design\DesignRevision2;
use Rialto\Stock\Item\StockItem;

/**
 * For testing.
 */
class FakeMadisonClient extends MadisonClient
{
    const PRODUCT_URL = 'http://store.example.com/products/test-product';

    /** @var Exception */
    private $exception = null;

    /**
     * @var StockItem[]
     */
    private $versionUpdated = [];

    /** @noinspection PhpMissingParentConstructorInspection */
    public function __construct()
    {
        // skip parent constructor
    }

    public function createOrUpdateBoardProduct(StockItem $board,
                                               DesignRevision2 $designRevision): string
    {
        $this->throwIfRequested();
        return self::PRODUCT_URL;
    }

    public function getFeatures()
    {
        $this->throwIfRequested();
        return [
            '001' => [
                'code' => '001',
                'name' => 'A feature',
                'units' => 'mm',
            ],
        ];
    }

    public function updateCurrentVersion(StockItem $item)
    {
        $this->throwIfRequested();
        $this->versionUpdated[] = $item;
    }

    public function isVersionUpdated(StockItem $item)
    {
        return in_array($item, $this->versionUpdated, $strict = true);
    }

    /**
     * Call this if you want to simulate Madison errors.
     */
    public function setException(Exception $ex)
    {
        $this->exception = $ex;
    }

    private function throwIfRequested()
    {
        if ($this->exception) {
            throw $this->exception;
        }
    }
}
