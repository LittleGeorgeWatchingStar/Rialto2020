<?php

namespace Rialto\Accounting\Ledger\Account\Web;


use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\View\View;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Database\Orm\EntityList;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class GLAccountController extends RialtoController
{
    /**
     * @Route("/accounting/gl-account/")
     * @Method("GET")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $repo = $this->getRepository(GLAccount::class);
        $filters = $request->query->all();
        $list = new EntityList($repo, $filters);
        return View::create(GLAccountSummary::fromList($list));
    }

    /**
     * @Route("/accounting/gl-account/{id}/")
     * @Route("/accounting/gl-account/{id}")
     * @Method("GET")
     */
    public function viewAction(GLAccount $account)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        return View::create(new GLAccountSummary($account));
    }
}
