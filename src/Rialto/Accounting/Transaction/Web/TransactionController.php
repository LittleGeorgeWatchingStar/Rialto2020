<?php

namespace Rialto\Accounting\Transaction\Web;

use Doctrine\ORM\EntityRepository;
use FOS\RestBundle\View\View;
use Rialto\Accounting\Debtor\DebtorInvoice;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Accounting\Debtor\Orm\DebtorTransactionRepository;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Accounting\Transaction\TransactionRepo;
use Rialto\Database\Orm\EntityList;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Shows the GL entries and stock
 */
class TransactionController extends RialtoController
{
    /**
     * @Route("/accounting/transaction/", name="accounting_transaction_list")
     * @Method("GET")
     * @Template("accounting/transaction/list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $form = $this->createForm(TransactionListFilterType::class);
        $form->submit($request->query->all());
        $results = new EntityList($this->repo(), $form->getData());
        return [
            'form' => $form->createView(),
            'transactions' => $results,
        ];
    }

    /** @return TransactionRepo|EntityRepository */
    private function repo()
    {
        return $this->getRepository(Transaction::class);
    }

    /**
     * @Route("/accounting/transaction/{trans}/",
     *     name="transaction_view",
     *     options={"expose"=true})
     * @Method("GET")
     * @Template("accounting/transaction/view.html.twig")
     */
    public function viewAction(Transaction $trans)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        /** @var DebtorTransactionRepository $debtorTransactionRepo */
        $debtorTransactionRepo = $this->getRepository(DebtorTransaction::class);
        /** @var DebtorInvoice[] $debtorInvoices */
        $debtorInvoices = $debtorTransactionRepo->findDebtorInvoicesForTransaction($trans);
        $salesOrders = array_map(function (DebtorInvoice $transaction) {
            return $transaction->getSalesOrder();
        }, $debtorInvoices);
        return [
            'entity' => $trans,
            'salesOrders' => $salesOrders
        ];
    }

    /**
     * Form for manually creating a new Transaction.
     *
     * @Route("/accounting/create-transaction/", name="transaction_create")
     * @Method("GET")
     * @Template("accounting/transaction/create.html.twig")
     */
    public function createAction()
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        return [
            'sysType' => SystemType::fetch(SystemType::JOURNAL, $this->dbm),
        ];
    }

    /**
     * Manually create a new Transaction.
     *
     * @Route("/accounting/transaction/")
     * @Route("/accounting/transaction")
     * @Method("POST")
     */
    public function postAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $form = $this->createForm(TransactionTemplateType::class);
        $form->submit($request->request->all());
        if ($form->isValid()) {
            /** @var $template TransactionTemplate */
            $template = $form->getData();
            $sysType = SystemType::fetch(SystemType::JOURNAL, $this->dbm);
            $this->dbm->beginTransaction();
            try {
                $trans = $template->createTransaction($sysType);
                $this->dbm->persist($trans);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
            return View::create([
                'id' => $trans->getId(),
            ], Response::HTTP_CREATED);
        }

        return View::create($form, Response::HTTP_BAD_REQUEST)
            ->setFormat('json');
    }
}
