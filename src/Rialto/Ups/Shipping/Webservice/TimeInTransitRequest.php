<?php

namespace Rialto\Ups\Shipping\Webservice;

use DateTime;
use Gumstix\GeographyBundle\Model\PostalAddress;
use Symfony\Component\Templating\EngineInterface as TemplatingEngine;

class TimeInTransitRequest extends UpsXmlRequest
{
    /** @var PostalAddress */
    private $from;

    /** @var PostalAddress */
    private $to;

    /** @var DateTime */
    private $pickup;

    public function __construct(PostalAddress $from,
                                PostalAddress $to,
                                DateTime $pickup)
    {
        $this->from = $from;
        $this->to = $to;
        $this->pickup = $pickup;
    }

    public function getName()
    {
        return 'TimeInTransit';
    }

    public function render(TemplatingEngine $templating)
    {
        return $templating->render('ups/shipping/webservice/TimeInTransit.xml.twig', [
            'from' => $this->from,
            'to' => $this->to,
            'pickup' => $this->pickup,
        ]);
    }
}