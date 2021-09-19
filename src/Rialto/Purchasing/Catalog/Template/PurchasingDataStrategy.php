<?php

namespace Rialto\Purchasing\Catalog\Template;

use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A strategy for automatically creating purchasing data records for
 * a stock item.
 *
 * @see PurchasingData
 */
abstract class PurchasingDataStrategy
{
    /**
     * @return string[]
     */
    private static function getStrategyClasses(): array
    {
        return [
            CustomPcbStrategy::class,
            CustomBoardStrategy::class,
            OmegaCustomBoardStrategy::class,
        ];
    }

    /**
     * @return PurchasingDataStrategy[]
     */
    public static function getStrategyInstances(): array
    {
        return array_map(function ($class) {
            return new $class;
        }, self::getStrategyClasses());
    }

    /**
     * @return string[]
     */
    public static function getStrategyNames(): array
    {
        return array_map(function ($class) {
            /** @var PurchasingDataStrategy $strategy */
            $strategy = new $class;
            return $strategy->getName();
        }, self::getStrategyClasses());
    }

    /**
     * @throws \UnexpectedValueException if $string is not valid.
     */
    public static function create(string $string): PurchasingDataStrategy
    {
        foreach (self::getStrategyInstances() as $instance) {
            if ($instance->getName() === $string) {
                return $instance;
            }
        }

        throw new \UnexpectedValueException("Unknown strategy $string");
    }

    public abstract function getName();

    public function __toString()
    {
        return $this->getName();
    }

    public abstract function getDescription();

    /**
     * @return string[]
     */
    public abstract function getVariableNames(): array;

    /**
     * @return string[]
     */
    protected abstract function getVariableTypes(): array;

    /**
     * @return string[]
     */
    public abstract function getVariableFormTypes(): array;

    /**
     * @return Constraint[]
     */
    public function getVariableConstraints(): array
    {
        $contraints = [];
        foreach ($this->getVariableNames() as $index => $name) {
            $type = $this->getVariableTypes()[$index];
            $contraints[$name] = new Assert\Type(['type' => $type, 'message' => "$name should be type {{ type }}."]);
        }
        return $contraints;
    }

    public abstract function appliesTo(StockItem $item);
    public abstract function createPurchasingData(PurchasingDataTemplate $template,
                                                  ItemVersion $version): PurchasingData;
}
