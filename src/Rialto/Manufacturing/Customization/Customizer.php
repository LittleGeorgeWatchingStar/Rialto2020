<?php

namespace Rialto\Manufacturing\Customization;


use Rialto\Manufacturing\Bom\Bom;
use Rialto\Stock\ItemIndex;

/**
 * Applies customization strategies to a BOM to arrive at the final
 * customization.
 */
class Customizer
{
    /** @var CustomizationStrategy[] */
    private $strategies = [];

    /** @var string[] */
    private $errors = [];

    public function register($name, CustomizationStrategy $strategy)
    {
        $this->strategies[$name] = $strategy;
    }

    /** @return CustomizationStrategy[] */
    public function getRegisteredStrategies()
    {
        return $this->strategies;
    }

    public function customize(ItemIndex $bom, Customization $cmz)
    {
        $this->errors = [];
        $cmz->applySubstitutions($bom);
        $strategies = $this->getStrategies($cmz);
        foreach ($strategies as $strategy) {
            $strategy->apply($bom);
        }
        foreach ($strategies as $strategy) {
            $this->errors = array_merge($this->errors, $strategy->check($bom));
        }
    }

    /**
     * Create a copy of the given Bom with the Customization applied to it.
     */
    public function generateCustomizedBom(Bom $bom,
                                          Customization $customization): Bom
    {
        $copy = $bom->createCopy();
        $this->customize($copy, $customization);
        return $copy;
    }

    /** @return CustomizationStrategy[] */
    private function getStrategies(Customization $cmz)
    {
        return array_map(function ($name) {
            return $this->strategies[$name];
        }, $cmz->getStrategies());
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
