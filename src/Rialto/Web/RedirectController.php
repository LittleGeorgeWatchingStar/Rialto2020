<?php

namespace Rialto\Web;


use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Redirects form submissions into route URLs.
 */
class RedirectController extends RialtoController
{
    /**
     * @Route("/redirect/", name="redirect_to_route")
     * @Method("GET")
     */
    public function redirectAction(Request $request)
    {
        $params = $request->query->all();
        $routeName = $params['route'];
        unset($params['route']);

        return $this->redirectToRoute($routeName, $params);
    }
}
