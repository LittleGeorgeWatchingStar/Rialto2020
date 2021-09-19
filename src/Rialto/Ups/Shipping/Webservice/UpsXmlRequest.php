<?php

namespace Rialto\Ups\Shipping\Webservice;

use SimpleXMLElement;
use Symfony\Component\Templating\EngineInterface as TemplatingEngine;

/**
 * Base class for all UPS XML request types.
 */
abstract class UpsXmlRequest
{
    /** @var SimpleXmlElement */
    protected $xml;

    public function setXml($xmlString)
    {
        try {
            $this->xml = new SimpleXMLElement($xmlString);
        }
        catch ( \Exception $ex ) {
            throw new \InvalidArgumentException(sprintf(
                'parameter to %s is not valid: %s',
                __METHOD__, $ex->getMessage() ),
                $ex->getCode(),
                $ex
            );
        }
    }

    /**
     * @return string
     *         eg, "Rate", "ShipConfirm", "ShipAccept".
     */
    public abstract function getName();

    /**
     * Renders this request into an XML string that is ready to be sent.
     *
     * @return string
     */
    public abstract function render(TemplatingEngine $templating);
}
