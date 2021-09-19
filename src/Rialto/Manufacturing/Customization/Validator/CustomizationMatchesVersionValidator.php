<?php

namespace Rialto\Manufacturing\Customization\Validator;

use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\Customization\Substitution;
use Rialto\Stock\Item\Version\ItemVersion;
use Rialto\Stock\VersionedItem;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @see CustomizationMatchesVersion
 */
class CustomizationMatchesVersionValidator extends ConstraintValidator
{
    /**
     * @param VersionedItem $item
     * @param CustomizationMatchesVersion $constraint
     */
    public function validate($item, Constraint $constraint)
    {
        assertion($item instanceof VersionedItem);

        $cmz = $item->getCustomization();
        if (! $cmz) {
            return;
        }

        $version = $item->getVersion();
        if ($version->isSpecified()) {
            $version = $item->getStockItem()->getVersion($version);
            $this->validateSubstitutionsMatchVersion($version, $cmz);
        } else {
            $this->context->buildViolation(
                "Customization '$cmz' cannot be guaranteed to match an unspecified version.")
                ->atPath('customization')
                ->addViolation();
        }
    }

    private function validateSubstitutionsMatchVersion(
        ItemVersion $version,
        Customization $cmz)
    {
        foreach ($cmz->getSubstitutions() as $sub) {
            $this->validateSubstitutionMatchesVersion($sub, $version);
        }
    }

    private function validateSubstitutionMatchesVersion(
        Substitution $sub,
        ItemVersion $version)
    {
        $this->validateDnpMatchesVersion($sub, $version);
        $this->validateAddMatchesVersion($sub, $version);
    }

    private function validateDnpMatchesVersion(
        Substitution $sub,
        ItemVersion $version)
    {
        if (! ($sub->isDnp() || $sub->isSwapAll())) {
            return;
        }

        $component = $sub->getDnpComponent();
        $bomItem = $version->getBomItem($component);
        if (! $bomItem) {
            $v = $version->getFullSku();
            $this->context->buildViolation(
                "$v is not compatible with $sub because it does not contain $component.")
                ->atPath('customization')
                ->addViolation();
            return;
        }

        foreach ($sub->getDnpDesignators() as $des) {
            if (! in_array($des, $bomItem->getDesignators())) {
                $this->context->buildViolation(
                    "$bomItem does not contain designator '$des'.")
                    ->atPath('customization')
                    ->addViolation();
            }
        }
    }

    private function validateAddMatchesVersion(
        Substitution $sub,
        ItemVersion $version)
    {
        if (! $sub->isAddition()) {
            return;
        }

        $component = $sub->getAddComponent();
        $bomItem = $version->getBomItem($component);
        if (! $bomItem) {
            return;
        }

        foreach ($sub->getAddDesignators() as $des) {
            if (in_array($des, $bomItem->getDesignators())) {
                $this->context->buildViolation(
                    "$bomItem already contains designator '$des'.")
                    ->atPath('customization')
                    ->addViolation();
            }
        }
    }
}
