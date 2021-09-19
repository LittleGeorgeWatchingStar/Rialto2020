<?php

namespace Rialto\Accounting\Debtor\Credit\Web;

use Rialto\Accounting\Debtor\Credit\CreditNote;
use Rialto\Accounting\Debtor\DebtorTransactionFactory;
use Rialto\Sales\Customer\Customer;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class CreditNoteController extends RialtoController
{
    /**
     * @Route("/Debtor/CreditNote/{id}/create",
     *   name="Debtor_CreditNote_create")
     * @Template("accounting/debtor/credit/create.html.twig")
     */
    public function createAction(Customer $customer, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $creditNote = new CreditNote($customer);

        $form = $this->createForm(CreditNoteType::class, $creditNote, [
            'customer' => $customer,
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                /** @var DebtorTransactionFactory $factory */
                $factory = $this->get(DebtorTransactionFactory::class);
                $debtorTrans = $factory->createCredit($creditNote);
                $this->dbm->flushAndCommit();
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }

            $this->logNotice(sprintf('Created %s.',
                strtolower($debtorTrans->getLabel())
            ));
            return $this->redirectToRoute('debtor_transaction_view', [
                'trans' => $debtorTrans->getId(),
            ]);
        }

        return [
            'customer' => $customer,
            'form' => $form->createView(),
            'cancelUri' => $this->generateUrl('customer_view', [
                'customer' => $customer->getId(),
            ]),
        ];
    }

}
