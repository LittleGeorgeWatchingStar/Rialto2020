<?php

namespace Rialto\Purchasing\Catalog\Template;

use Rialto\Purchasing\Catalog\CostBreak;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;


/**
 * A PurchasingDataStrategy for custom PCBs.
 */
class CustomPcbStrategy extends PurchasingDataStrategy
{
    const STRATEGY_NAME = 'CustomPcbStrategy';
    const DESCRIPTION = 'PCB unit cost = (template unit cost) x (PCB surface area)';

    const VARIABLE_NAMES = [
        'minimumOrderQty',
        'manufacturerLeadTime',
        'supplierLeadTime',
        'unitCost'
    ];

    const VARIABLE_TYPES = [
        'integer',
        'integer',
        'integer',
        'float',
    ];

    const VARIABLE_FORM_TYPES = [
        IntegerType::class,
        IntegerType::class,
        IntegerType::class,
        NumberType::class,
    ];

    public function getName()
    {
        return self::STRATEGY_NAME;
    }

    public function getDescription()
    {
        return self::DESCRIPTION;
    }

    /**
     * @return string[]
     */
    public function getVariableNames(): array
    {
        return self::VARIABLE_NAMES;
    }

    /**
     * @return string[]
     */
    protected function getVariableTypes(): array
    {
        return self::VARIABLE_TYPES;
    }

    /**
     * @return string[]
     */
    public function getVariableFormTypes(): array
    {
        return self::VARIABLE_FORM_TYPES;
    }

    public function appliesTo(StockItem $item)
    {
        return $item->isPCB();
    }

    public function createPurchasingData(PurchasingDataTemplate $template,
                                         ItemVersion $version): PurchasingData
    {
        $item = $version->getStockItem();
        $purchData = new PurchasingData($item);
        $purchData->setVersion($version);
        $purchData->setSupplier($template->getSupplier());
        $purchData->setCatalogNumber($version->getFullSku());
        $purchData->setIncrementQty($template->getIncrementQty());
        $purchData->setBinStyle($template->getBinStyle());
        $purchData->setBinSize($template->getBinSize());

        $variables = $template->getVariables();
        $minOrderQty = $variables['minimumOrderQty'];
        $manufacturerLeadTime = $variables['manufacturerLeadTime'];
        $supplierLeadTime = $variables['supplierLeadTime'];
        $unitCost = $variables['unitCost'];

        $costBreak = new CostBreak();
        $costBreak->setMinimumOrderQty($minOrderQty);
        $costBreak->setManufacturerLeadTime($manufacturerLeadTime);
        $costBreak->setSupplierLeadTime($supplierLeadTime);
        $costBreak->setUnitCost($this->calculateNumUnits($version) * $unitCost);
        $purchData->addCostBreak($costBreak);

        return $purchData;
    }

    private function calculateNumUnits(ItemVersion $version)
    {
        $dim = $version->getDimensions();
        return $dim->getX() * $dim->getY();
    }

}
