<?php

namespace Rialto\Ups\Shipping\Export;

use Rialto\Shipping\Export\DeniedPartyResponse;
use Rialto\Util\Strings\TextFormatter;

/**
 * UPS frequently returns XML documents with control characters in them,
 * which PHP's Soap module cannot handle. So this class is an ugly workaround.
 */
class InvalidXmlResponse implements DeniedPartyResponse
{
    /** @var string */
    private $response;

    public function __construct($response)
    {
        $this->response = $response;
    }

    public function getMatchingParties()
    {
        $formatter = new TextFormatter();
        $party = $formatter->stripControlCharacters($this->response);
        $xml = new \DOMDocument();
        $xml->loadXML($party);
        $xml->formatOutput = true;
        return [$xml->saveXML()];
    }

    public function hasDeniedParties()
    {
        return true;
    }
}
