<?php

namespace Rialto\Ups\Shipping\Webservice;

use Symfony\Component\Templating\EngineInterface as TemplatingEngine;

class ShipAcceptRequest
extends UpsXmlRequest
{
    private $shipmentDigest;

    public function __construct($digest)
    {
        $this->shipmentDigest = $digest;
    }

    public function getName()
    {
        return 'ShipAccept';
    }

    public function render(TemplatingEngine $templating)
    {
        return $templating->render('ups/shipping/webservice/ShipAccept.xml.twig', [
            'digest' => $this->shipmentDigest,
        ]);
    }
}
