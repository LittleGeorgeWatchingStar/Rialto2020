<?php

namespace Rialto\Ups\Shipping\Webservice;

use Rialto\Ups\UpsAccount;
use Symfony\Component\Templating\EngineInterface as TemplatingEngine;

/**
 * XML request used to authenticate with UPS webservices.
 */
class AccessRequest
extends UpsXmlRequest
{
    const NAME = 'AccessRequest';

    /** @var UpsAccount */
    private $account;

    public function __construct(UpsAccount $account)
    {
        $this->account = $account;
    }

    public function getName()
    {
        return self::NAME;
    }

    public function render(TemplatingEngine $templating)
    {
        return $templating->render('ups/shipping/webservice/AccessRequest.xml.twig', [
            'account' => $this->account
        ]);
    }

}
