<?php

namespace Rialto\Ups\Shipping\Webservice;

use DOMDocument;
use DOMXPath;
use Rialto\Web\ControllerTestCase;
use Symfony\Component\Templating\EngineInterface as TemplatingEngine;


abstract class UpsXmlRequestTestCase
extends ControllerTestCase
{
    /** @var TemplatingEngine */
    protected $templating;

    public function setUp()
    {
        parent::setUp();
        $this->templating = $this->container->get('templating');
    }

    protected function assertIsXmlString($string)
    {
        $this->assertStringStartsWith('<?xml', $string);
    }

    protected function assertElementExists($xmlString, $xpathQuery)
    {
        $list = $this->getElements($xmlString, $xpathQuery);
        $this->assertGreaterThanOrEqual(1, $list->length, "$xpathQuery not found");
    }

    private function getElements($xmlString, $xpathQuery)
    {
        $xpath = $this->getXpath($xmlString);
        return $xpath->query($xpathQuery);
    }

    private function getXpath($xmlString)
    {
        $doc = new DOMDocument();
        $doc->loadXML($xmlString);
        return new DOMXPath($doc);
    }

    protected function assertNumElementsEquals($xmlString, $xpathQuery, $expectedNum)
    {
        $list = $this->getElements($xmlString, $xpathQuery);
        $this->assertEquals($expectedNum, $list->length);
    }

    protected function assertElementDoesNotExist($xmlString, $xpathQuery)
    {
        $list = $this->getElements($xmlString, $xpathQuery);
        $this->assertEquals(0, $list->length);
    }

    protected function getElementValue($xmlString, $xpathQuery)
    {
        $list = $this->getElements($xmlString, $xpathQuery);
        $firstElem = $list->item(0);
        return $firstElem->textContent;
    }

    protected function assertElementValueEquals($xmlString, $xpathQuery, $expectedValue)
    {
        $actualValue = $this->getElementValue($xmlString, $xpathQuery);
        $this->assertEquals($expectedValue, $actualValue);
    }
}
