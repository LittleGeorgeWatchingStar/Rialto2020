<?php

namespace Rialto\Accounting\Bank\Account\Web;


use FOS\RestBundle\View\View;
use Rialto\Accounting\Bank\Account\BankAccount;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class BankAccountController extends RialtoController
{
    /**
     * @Route("/accounting/bank-account/")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $repo = $this->getRepository(BankAccount::class);
        $list = $repo->findAll();
        return View::create(BankAccountSummary::fromList($list));
    }

    /**
     * @Route("/accounting/bank-account/{id}/")
     * @Method("GET")
     */
    public function viewAction(BankAccount $account)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        return View::create(new BankAccountSummary($account));
    }
}
