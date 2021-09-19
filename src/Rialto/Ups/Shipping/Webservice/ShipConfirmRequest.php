<?php

namespace Rialto\Ups\Shipping\Webservice;

use Rialto\Shipping\Export\Document\ElectronicExportInformation;
use Rialto\Shipping\Shipment\Document\ShipmentInvoice;
use Rialto\Ups\Shipping\UpsShipment;
use Symfony\Component\Templating\EngineInterface as TemplatingEngine;


class ShipConfirmRequest extends UpsXmlRequest
{
    const PACKAGING_TYPE_CUSTOMER_SUPPLIED = '02';

    const UNIT_OF_MEASUREMENT_CODE_EACH = 'EA';
    const UNIT_OF_MEASUREMENT_CODE_KILOGRAMS = 'KGS';
    const UNIT_OF_MEASUREMENT_CODE_NUMBER = 'NO';

    const FORM_CODE_INVOICE = '01';
    const FORM_CODE_SED = '02';

    /** @var UpsShipment */
    private $shipment;

    public function __construct(UpsShipment $shipment)
    {
        $this->shipment = $shipment;
    }

    public function getName()
    {
        return 'ShipConfirm';
    }

    public function render(TemplatingEngine $templating)
    {
        return $templating->render('ups/shipping/webservice/ShipConfirm.xml.twig', [
            'shipment' => $this->shipment,
            'invoice' => new ShipmentInvoice($this->shipment),
            'eei' => new ElectronicExportInformation($this->shipment),
        ]);
    }
}
