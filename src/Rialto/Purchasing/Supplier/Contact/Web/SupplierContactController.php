<?php

namespace Rialto\Purchasing\Supplier\Contact\Web;

use Rialto\Purchasing\Supplier\Contact\SupplierContact;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Purchasing\Web\PurchasingRouter;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for maintaining supplier contacts.
 */
class SupplierContactController extends RialtoController
{
    /**
     * @var PurchasingRouter
     */
    private $router;

    protected function init(ContainerInterface $container)
    {
        $this->router = $container->get(PurchasingRouter::class);
    }

    /**
     * @Route("/Purchasing/SupplierContact", name="Purchasing_SupplierContact_create")
     * @Template("purchasing/supplierContact/contact-edit.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        /** @var Supplier $supplier */
        $supplier = $this->needEntityFromRequest(Supplier::class, 'supplierId', $request);
        $contact = SupplierContact::create($supplier);
        $heading = 'Create new contact for ' . $supplier->getName();
        return $this->renderForm($contact, $heading, $request);
    }

    /**
     * @Route("/Purchasing/SupplierContact/{id}", name="Purchasing_SupplierContact_edit")
     * @Template("purchasing/supplierContact/contact-edit.html.twig")
     */
    public function editAction(SupplierContact $contact, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::PURCHASING);
        $heading = 'Edit ' . $contact->getName();
        return $this->renderForm($contact, $heading, $request);
    }

    private function renderForm(SupplierContact $contact, $heading, Request $request)
    {
        $supplier = $contact->getSupplier();
        $form = $this->createForm(SupplierContactType::class, $contact);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->persist($contact);
            $this->dbm->flush();
            $this->logNotice(sprintf('Contact %s updated successfully.',
                $contact->getName()
            ));
            $uri = $this->supplierUrl($supplier);
            return $this->redirect($uri);
        }

        return [
            'form' => $form->createView(),
            'heading' => $heading,
            'cancelUri' => $this->supplierUrl($supplier),
        ];
    }

    private function supplierUrl(Supplier $supplier)
    {
        return $this->router->supplierView($supplier);
    }
}
