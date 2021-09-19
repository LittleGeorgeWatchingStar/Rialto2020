<?php

namespace Rialto\Accounting\Supplier\Web;

use Rialto\Accounting\Supplier\SupplierCreditNote;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * For entering credit notes from suppliers.
 */
class SupplierCreditNoteController extends RialtoController
{
    /**
     * @Route("/Accounting/Supplier/{id}/creditNote/",
     *   name="Accounting_Supplier_creditNote")
     * @Template("accounting/suppliercreditnote/note-create.html.twig")
     */
    public function createAction(Supplier $supplier, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $company = $this->getDefaultCompany();
        $credit = new SupplierCreditNote($supplier, $company->getCreditorsAccount());
        $form = $this->createForm(SupplierCreditNoteType::class, $credit);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() ) {
            $this->dbm->beginTransaction();
            try {
                $suppTrans = $credit->createTransaction($this->dbm);
                $this->dbm->flushAndCommit();
            } catch ( \Exception $ex ) {
                $this->dbm->rollBack();
                throw $ex;
            }
            $this->logNotice("Create $suppTrans successfully.");
            return $this->redirectToRoute('supplier_transaction_view', [
                'trans' => $suppTrans->getId(),
            ]);
        }
        return [
            'supplier' => $supplier,
            'form' => $form->createView(),
            'cancelUri' => $this->generateUrl('supplier_view', [
                'supplier' => $supplier->getId(),
            ]),
        ];
    }
}
