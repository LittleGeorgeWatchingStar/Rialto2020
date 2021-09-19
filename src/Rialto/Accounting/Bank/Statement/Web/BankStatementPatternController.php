<?php

namespace Rialto\Accounting\Bank\Statement\Web;

use Rialto\Accounting\Bank\Statement\BankStatementPattern;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class BankStatementPatternController extends RialtoController
{
    /**
     * @Route("/accounting/bank-statement-pattern/",
     *     name="bank_statement_pattern_list")
     * @Method("GET")
     * @Template("accounting/bankstatement/pattern/list.html.twig")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $patterns = $this->getRepository(BankStatementPattern::class)
            ->findAll();
        return ['pattern' => $patterns];
    }

    /**
     * @Route("/Accounting/BankStatementPattern",
     *     name="Accounting_BankStatementPattern_create")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $strategy = $request->get('strategy');
        if (!$strategy) {
            throw $this->badRequest("Missing parameter 'strategy'");
        }
        $pattern = BankStatementPattern::create($strategy);

        return $this->updateBankStatementPattern($pattern, $request);
    }

    /**
     * @Route("/Accounting/BankStatementPattern/{id}",
     *     name="Accounting_BankStatementPattern_edit")
     */
    public function editAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        /** @var BankStatementPattern $pattern */
        $pattern = $this->dbm->need(BankStatementPattern::class, $id);
        return $this->updateBankStatementPattern($pattern, $request);
    }

    private function updateBankStatementPattern(BankStatementPattern $pattern,
                                                Request $request)
    {
        $form = $this->createForm(BankStatementPatternType::class, $pattern);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->persist($pattern);
            $this->dbm->flush();
            $this->logNotice("The bank statement pattern has been modified successfully!");
            return $this->redirectToRoute('Accounting_BankStatementPattern_edit', [
                'id' => $pattern->getId(),
            ]);
        }
        return $this->render('accounting/bankstatement/pattern/edit.html.twig', [
            'form' => $form->createView(),
            'bankStatementPattern' => $pattern,
        ]);
    }

    /**
     * @Route("/record/Accounting/BankStatementPattern/{id}",
     *     name="Accounting_BankStatementPattern_delete")
     * @Method("DELETE")
     */
    public function deleteAction($id)
    {
        $this->denyAccessUnlessGranted(Role::ACCOUNTING);
        $pattern = $this->dbm->need(BankStatementPattern::class, $id);
        $this->dbm->remove($pattern);
        $this->dbm->flush();
        $this->logNotice("Deleted bank statement pattern $id.");
        return $this->redirectToRoute('bank_statement_pattern_list');
    }
}
