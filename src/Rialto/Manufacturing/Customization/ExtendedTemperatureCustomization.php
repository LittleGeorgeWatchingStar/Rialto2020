<?php

namespace Rialto\Manufacturing\Customization;

use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\Component\Component;
use Rialto\Manufacturing\Customization\Orm\SubstitutionRepository;
use Rialto\Measurement\Temperature\TemperatureRange;
use Rialto\Stock\ItemIndex;

/**
 * Replaces all standard-temperature parts in the BOM with their extended-
 * temperature equivalents.
 */
class ExtendedTemperatureCustomization implements CustomizationStrategy
{
    /** @var SubstitutionRepository */
    private $subRepo;

    /** @var TemperatureRange */
    private $requiredRange;

    public function __construct(DbManager $dbm, $minTemp, $maxTemp)
    {
        $this->subRepo = $dbm->getRepository(Substitution::class);
        $this->requiredRange = new TemperatureRange($minTemp, $maxTemp);
    }

    public function __toString()
    {
        return 'Extended temperature';
    }

    public function apply(ItemIndex $bom)
    {
        $substitutions = $this->subRepo->findExtendedTemperature();
        foreach ($substitutions as $sub) {
            $remove = $sub->getDnpComponent();
            if ($bom->contains($remove)) {
                $sub->applyToBom($bom);
            }
        }
    }

    public function check(ItemIndex $bom)
    {
        $errors = [];
        foreach ($bom as $component) {
            $errors[] = $this->checkTempRange($component);
        }
        return array_filter($errors);
    }

    private function checkTempRange(Component $component)
    {
        $range = $component->getTemperatureRange();
        $sku = $component->getFullSku();
        if (!$range->isSpecified()) {
            return "Temperature range for $sku is not specified.";
        } elseif (!$this->requiredRange->isWithin($range)) {
            return "$sku cannot tolerate {$this->requiredRange}; its range is $range.";
        }
        return null;
    }

}
