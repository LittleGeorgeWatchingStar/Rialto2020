<?php

namespace Rialto\Purchasing\Catalog\Template;

use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Purchasing\Catalog\CostBreak;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class OmegaCustomBoardStrategy extends PurchasingDataStrategy
{
    const STRATEGY_NAME = 'OmegaCustomBoardStrategy';
    const DESCRIPTION = 'Board unit cost = (smt unit cost) x (# of smts) + (thru-hole unit cost) x (# of thru-holes) **Total smt costs for the order is subjected to min lot charge.';

    const VARIABLE_NAMES = [
        'minimumOrderQty',
        'manufacturerLeadTime',
        'supplierLeadTime',
        'smtUnitCost',
        'throughHoleUnitCost'
    ];

    const VARIABLE_TYPES = [
        'integer',
        'integer',
        'integer',
        'float',
        'float',
        'float',
    ];

    const VARIABLE_FORM_TYPES = [
        IntegerType::class,
        IntegerType::class,
        IntegerType::class,
        NumberType::class,
        NumberType::class,
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
        return $item->isBoard() && $item->isManufactured();
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
        $smtUnitCost = $variables['smtUnitCost'];
        $throughHoleUnitCost = $variables['throughHoleUnitCost'];

        $this->calcSmtAndThroughHoleCount($version, $smtCount, $throughHoleCount);

        $smtCost = $smtCount * $smtUnitCost;
        $throughHoleCost = $throughHoleCount * $throughHoleUnitCost;
        $finalUnitCost = $smtCost + $throughHoleCost;

        $finalCostBreak = new CostBreak();
        $finalCostBreak->setMinimumOrderQty($minOrderQty);
        $finalCostBreak->setManufacturerLeadTime($manufacturerLeadTime);
        $finalCostBreak->setSupplierLeadTime($supplierLeadTime);
        $finalCostBreak->setUnitCost($finalUnitCost);
        $purchData->addCostBreak($finalCostBreak);

        return $purchData;
    }

    /**
     * NOTE: This cost assumes "min lot charge" is met!
     */
    public function getModuleStandardLabourCost(PurchasingDataTemplate $template,
                                                ItemVersion $version): float
    {
        $variables = $template->getVariables();
        $smtUnitCost = $variables['smtUnitCost'];
        $throughHoleUnitCost = $variables['throughHoleUnitCost'];

        $this->calcSmtAndThroughHoleCount($version, $smtCount, $throughHoleCount);

        $smtCost = $smtCount * $smtUnitCost;
        $throughHoleCost = $throughHoleCount * $throughHoleUnitCost;

        return $smtCost + $throughHoleCost;
    }

    private function calcSmtAndThroughHoleCount(ItemVersion $version,
                                                ?int &$smtCount,
                                                ?int &$throughHoleCount)
    {
        $smtCount = 0;
        $throughHoleCount = 0;
        foreach ($version->getBomItems() as $bomItem) {
            if (!$bomItem->getCategory()->isPart()) {
                continue;
            }
            $workType = $bomItem->getWorkType();
            // Null is the default, and also represents SMT.
            if ($workType === null) {
                $smtCount += $bomItem->getUnitQty();
            } else {
                switch ($workType->getId()) {
                    case WorkType::SMT:
                        $smtCount += $bomItem->getUnitQty();
                        break;
                    case WorkType::THROUGH_HOLE:
                        $throughHoleCount += $bomItem->getUnitQty();
                        break;
                }
            }
        }
    }
}
