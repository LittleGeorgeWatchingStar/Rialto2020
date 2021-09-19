<?php

namespace Rialto\Alert;

/**
 * Suggests to the user a way to resolve an AlertMessage.
 *
 * @see AlertMessage
 */
interface AlertResolution
{
    /** @return string */
    public function getText();
}
