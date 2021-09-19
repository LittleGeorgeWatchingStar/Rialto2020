<?php

namespace Rialto\Ups\Shipping\Webservice;

use Rialto\Sales\Order\SalesOrderInterface;
use Rialto\Shipping\Order\RatableOrder;
use Rialto\Ups\Shipping\UpsShipment;
use SimpleXMLElement;
use Symfony\Component\Templating\EngineInterface as TemplatingEngine;

/**
 * Base class for UPS Rate request types (Rate and Shop).
 */
abstract class RateRequestAbstract extends UpsXmlRequest
{
    private $order;

    private $accountNumber;

    /**
     * @param SalesOrderInterface $order
     */
    public function __construct(RatableOrder $order, $accountNumber)
    {
        $this->order = $order;
        $this->accountNumber = $accountNumber;
    }

    /**
     * Adds request option-specific values to the given xml object.
     *
     * @param SimpleXMLElement $xml
     */
    protected abstract function getRequestOption();

    /**
     * @see xml/UpsXmlRequest::getName()
     * @return string
     */
    public function getName()
    {
        return 'Rate';
    }

    public function render(TemplatingEngine $templating)
    {
        $params = $this->getTemplateParams();
        return $templating->render('ups/shipping/webservice/Rate.xml.twig', $params);
    }

    protected function getTemplateParams(): array
    {
        return [
            'option' => $this->getRequestOption(),
            'order' => $this->order,
            'packages' => UpsShipment::createPackages($this->order),
            'accountNumber' => $this->accountNumber,
        ];
    }
}
