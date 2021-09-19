<?php

namespace Rialto\Stock\Item;

use Rialto\Database\Orm\DbManager;
use Rialto\Stock\Category\StockCategory;
use Rialto\Tax\Authority\TaxAuthority;

/**
 * Creates new stock items from a StockItemTemplate.
 *
 * @see StockItem
 * @see StockItemTemplate
 */
class StockItemFactory
{
    /** @var DbManager */
    private $dbm;

    /** @var StockCodeGenerator */
    private $generator;

    public function __construct(DbManager $dbm, StockCodeGenerator $generator)
    {
        $this->dbm = $dbm;
        $this->generator = $generator;
    }


    /** @return StockItem */
    public function create(StockItemTemplate $template)
    {
        if (!$template->getStockCode()) {
            $this->generateStockCode($template);
        }
        if ($template->partValue && $template->package) {
            $this->generatePartSettings($template);
        }
        if (!$template->taxAuthority) {
            $template->taxAuthority = $this->getDefaultTaxAuthority();
        }
        $item = $this->instantiate($template);
        static $exclude = [
            'stockCode',
            'pattern',
            'mbFlag',
            'initialVersion',
        ];
        foreach (get_object_vars($template) as $field => $value) {
            if (in_array($field, $exclude)) {
                continue;
            }
            $method = 'set' . ucfirst($field);
            $item->$method($value);
        }
        $this->createInitialVersion($template, $item);
        return $item;
    }

    /** @return StockItem */
    private function instantiate(StockItemTemplate $template)
    {
        $sku = $template->getStockCode();
        switch ($template->mbFlag) {
            case StockItem::ASSEMBLY:
                return new AssemblyStockItem($sku);
            case StockItem::DUMMY:
                return new DummyStockItem($sku);
            case StockItem::MANUFACTURED:
                return new ManufacturedStockItem($sku);
            case StockItem::PURCHASED:
                return new PurchasedStockItem($sku);
            default:
                throw new \UnexpectedValueException("Invalid mbFlag {$template->mbFlag}");
        }
    }

    private function generateStockCode(StockItemTemplate $template)
    {
        $template->setStockCode($this->generator->generateNext($template->pattern));
    }

    private function generatePartSettings(StockItemTemplate $template)
    {
        if (!$template->mbFlag) {
            $template->mbFlag = StockItem::PURCHASED;
        }
        if (!$template->category) {
            $template->category = StockCategory::fetchPart($this->dbm);
        }
    }

    private function getDefaultTaxAuthority()
    {
        return $this->dbm->need(TaxAuthority::class, TaxAuthority::CA_STATE_TAX);
    }

    private function createInitialVersion(StockItemTemplate $template, StockItem $item)
    {
        $template->initialVersion->setStockItem($item);
        $template->initialVersion->setAutoBuildVersion(true);
        $template->initialVersion->setShippingVersion(true);
        $template->initialVersion->create();
    }
}
