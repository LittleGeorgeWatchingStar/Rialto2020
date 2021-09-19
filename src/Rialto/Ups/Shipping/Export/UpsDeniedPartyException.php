<?php

namespace Rialto\Ups\Shipping\Export;

use Rialto\NetworkException;
use Rialto\Shipping\Export\DeniedPartyException;
use SimpleXMLElement;


/**
 * UPS-specific implementation of DeniedPartyException.
 */
class UpsDeniedPartyException
extends NetworkException
implements DeniedPartyException
{
    private $response;

    public static function fromException(\Exception $original, $uri)
    {
        $message = sprintf(
            'An error occurred looking up denied party information: %s',
            $original->getMessage()
        );
        return new self($uri, $message, $original);
    }

    public function setResponse($response)
    {
        $this->response = $this->prepResponse($response);
    }

    private function prepResponse($response)
    {
        /* strip out non-printing characters */
        $response = preg_replace( '/[^[:print:]]/', '', $response);
        if (! $response ) return null;
        $dom = new \DOMDocument();
        $dom->loadXML($response);
        $dom->formatOutput = true;
        return $dom->saveXML();
    }

    /** @return SimpleXmlElement */
    public function getResponse()
    {
        return $this->response;
    }
}
