<?php

namespace Rialto\Ups\Shipping\Webservice;

use Rialto\Ups\Shipping\UpsShipment;

/**
 * An XML request to UPS that requests the shipping cost for the given order
 * and shipping method.
 */
class RateRequest extends RateRequestAbstract
{
    const REQUEST_OPTION = 'Rate';

    /**
     * The shipping method whose rate we want to find.
     */
    protected $method;

    public function __construct(UpsShipment $shipment)
    {
        $this->method = $shipment->getShippingMethod();
        parent::__construct($shipment, $shipment->getShipper()->getAccountNumber());
    }

    protected function getRequestOption()
    {
        return self::REQUEST_OPTION;
    }

    protected function getTemplateParams(): array
    {
        $params = parent::getTemplateParams();
        $params['method'] = $this->method;
        return $params;
    }
}
