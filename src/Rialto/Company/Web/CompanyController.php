<?php

namespace Rialto\Company\Web;


use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class CompanyController extends RialtoController
{
    /**
     * @Route("/company/", name="company_view")
     * @Method("GET")
     * @Template("core/company/view.html.twig")
     */
    public function viewAction()
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        return ['entity' => $this->getDefaultCompany()];
    }
}
