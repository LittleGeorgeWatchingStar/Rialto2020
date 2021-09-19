<?php

namespace Rialto\Purchasing\Recurring\Web;

use Rialto\Purchasing\Recurring\RecurringInvoice;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for managing recurring invoices.
 */
class RecurringInvoiceController extends RialtoController
{
    /**
     * @Route("/purchasing/recurring-invoice/", name="recurring_invoice_list")
     * @Method("GET")
     * @Template("purchasing/invoice/recurring/recurring-list.html.twig")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $recurring = $this->getRepository(RecurringInvoice::class)
            ->findAll();
        return [
            'recurringInvoice' => $recurring,
        ];
    }

    /**
     * @Route("/purchasing/new-recurring-invoice/", name="recurring_invoice_create")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $invoice = new RecurringInvoice();
        return $this->processForm($invoice, 'created', $request);
    }

    /**
     * @Route("/purchasing/recurring-invoice/{id}/", name="recurring_invoice_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(RecurringInvoice $invoice, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        return $this->processForm($invoice, 'updated', $request);
    }

    private function listUrl()
    {
        return $this->generateUrl('recurring_invoice_list');
    }

    private function processForm(RecurringInvoice $invoice, $updated, Request $request)
    {
        $form = $this->createForm(RecurringInvoiceType::class, $invoice);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->persist($invoice);
            $this->dbm->flush();
            $id = $invoice->getId();
            $this->logNotice("Recurring invoice $id $updated successfully.");
            return $this->redirect($this->listUrl());
        }

        return $this->render('purchasing/invoice/recurring/recurring-edit.html.twig', [
            'invoice' => $invoice,
            'form' => $form->createView(),
            'cancelUri' => $this->listUrl(),
        ]);
    }

    /**
     * @Route("/record/Purchasing/RecurringInvoice/{id}",
     *   name="Purchasing_RecurringInvoice_delete")
     * @Method("DELETE")
     */
    public function deleteAction(RecurringInvoice $invoice)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $id = $invoice->getId();
        $this->dbm->remove($invoice);
        $this->dbm->flush();
        $this->logNotice("Recurring invoice $id deleted successfully.");
        return $this->redirect($this->listUrl());
    }
}
