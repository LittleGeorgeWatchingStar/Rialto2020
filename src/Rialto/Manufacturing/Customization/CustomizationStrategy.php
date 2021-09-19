<?php

namespace Rialto\Manufacturing\Customization;

use Rialto\Stock\ItemIndex;

/**
 * Defines a complex customization that is not simply captured by a list of
 * substitutions.
 */
interface CustomizationStrategy
{
    /**
     * Modifies $bom by applying any changes.
     *
     * @param ItemIndex $bom
     */
    public function apply(ItemIndex $bom);

    /**
     * Checks the modified BOM to make sure it meets any requirements
     * of this customization.
     *
     * @param ItemIndex $bom
     * @return string[]
     */
    public function check(ItemIndex $bom);

    /**
     * @return string A user-friendly description of this strategy; eg:
     * "Extended temperature".
     */
    public function __toString();
}
