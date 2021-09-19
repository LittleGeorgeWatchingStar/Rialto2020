<?php

namespace Rialto\Ups\Shipping\Webservice;

use SimpleXMLElement;


/**
 * Base class for XML Response classes.
 */
abstract class UpsXmlResponse
{
    /** @var UpsXmlRequest */
    protected $request;

    /**
     * Parses the given XML response from the given request.
     */
    public function __construct(UpsXmlRequest $request, SimpleXMLElement $xml)
    {
        $this->request = $request;
        $this->parseErrorsFromXml($xml);
        $this->parseResults($xml);
    }

    private function parseErrorsFromXml(SimpleXMLElement $xml)
    {
        foreach ( $xml->Response->Error as $element ) {
            $this->parseError($element);
        }
    }

    private function parseError($element)
    {
        $error = new UpsXmlError($this->request, $element);
        if ( $error->isFatal() ) {
            throw $error;
        }
        else {
            error_log(sprintf(
                'UPS %s request returned a warning: %s',
                $this->request->getName(),
                $error->getDescription()
            ));
        }
    }

    /**
     * Parses the given XML string and populates any subclass-specific
     * fields with the XML string's contents.
     *
     * @abstract
     * @param SimpleXMLElement $xml
     */
    protected abstract function parseResults(SimpleXMLElement $xml);
}

