<?php

namespace Rialto\Allocation\Dispatch\Web;

use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allows the user to view and print allocation dispatch instructions.
 *
 * @see AllocationDispatchInstructions
 */
class DispatchInstructionsController extends RialtoController
{
    /**
     * The session key where the instructions are stored.
     * @var string
     */
    const SESSION_KEY = 'allocationDispatchInstructions';

    /**
     * @Route("/Allocation/DispatchInstructions")
     * @Template("allocation/dispatchInstruction/dispatch-form.html.twig")
     */
    public function formAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $session = $request->getSession();
        if (! $session->has(self::SESSION_KEY) ) return new Response('');
        $instructions = $session->get(self::SESSION_KEY);
        if ( count($instructions) == 0 ) return new Response('');

        $session->remove(self::SESSION_KEY);

        return [
            'instructions' => $instructions,
        ];
    }
}
