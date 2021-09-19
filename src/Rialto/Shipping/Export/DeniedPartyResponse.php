<?php

namespace Rialto\Shipping\Export;

/**
 * The response returned by DeniedPartyScreener->screen();
 *
 * @see DeniedPartyScreener
 */
interface DeniedPartyResponse
{
    /** @return boolean */
    public function hasDeniedParties();

    /** @return string[] */
    public function getMatchingParties();
}
