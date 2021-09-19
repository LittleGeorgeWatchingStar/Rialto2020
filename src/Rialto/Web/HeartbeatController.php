<?php

namespace Rialto\Web;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

/**
 * Implements an API "heartbeat" that clients can use to see if the server
 * is up.
 */
class HeartbeatController extends RialtoController
{
    /**
     * @Route("/api/")
     * @Method({"GET", "HEAD"})
     */
    public function getAction()
    {
        return new Response();
    }
}
