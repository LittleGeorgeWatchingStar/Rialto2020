<?php

namespace Rialto\Tax\Web;

use Rialto\Tax\TaxExemption;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Twig extension for the tax bundle.
 */
class TaxExtension extends AbstractExtension implements GlobalsInterface
{
    public function getGlobals()
    {
        return [
            'rialto_tax_exemptions' => TaxExemption::getChoices(),
        ];
    }

}
