<?php

namespace Rialto\Manufacturing\Customization;

/**
 * Anything which supports customizations.
 *
 * @see Customization
 */
interface Customizable
{
    /** @return Customization|null */
    public function getCustomization();
}
