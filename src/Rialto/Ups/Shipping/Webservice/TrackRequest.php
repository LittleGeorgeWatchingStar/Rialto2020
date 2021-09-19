<?php

namespace Rialto\Ups\Shipping\Webservice;

use Symfony\Component\Templating\EngineInterface as TemplatingEngine;

class TrackRequest extends UpsXmlRequest
{
    /** @var string */
    private $trackingNumber;

    public function __construct(string $trackingNumber)
    {
        $this->trackingNumber = $trackingNumber;
    }

    public function getName()
    {
        return 'Track';
    }

    public function render(TemplatingEngine $templating)
    {
        return $templating->render('ups/shipping/webservice/Track.xml.twig', [
            'trackingNumber' => $this->trackingNumber,
        ]);
    }
}