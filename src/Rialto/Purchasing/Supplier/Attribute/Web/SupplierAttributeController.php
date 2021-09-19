<?php

namespace Rialto\Purchasing\Supplier\Attribute\Web;

use Rialto\Entity\Web\AttributeBag;
use Rialto\Purchasing\Supplier\Attribute\Orm\SupplierAttributeRepository;
use Rialto\Purchasing\Supplier\Attribute\SupplierAttribute;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for viewing and manipulating supplier attributes.
 */
class SupplierAttributeController extends RialtoController
{
    /**
     * User interface for batch-editing all of the attributes of an entity.
     *
     * @Route("/attribute/purchasing/supplier/{id}/", name="supplier_attribute_edit")
     */
    public function editAction(Supplier $supplier, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $returnUri = $this->generateUrl('supplier_view', [
            'supplier' => $supplier->getId(),
        ]);
        $repo = $this->getAttributeRepo();
        $attributes = new AttributeBag($supplier, $repo->findByEntity($supplier));

        $form = $this->createFormBuilder($attributes)
            ->add('attributes', CollectionType::class, [
                'entry_type' => SupplierAttributeType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'label' => false,
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $attributes->persist($this->dbm);
            $this->dbm->flush();
            $this->logNotice("Attributes updated successfully.");
            return $this->redirect($returnUri);
        }

        return $this->render('core/entity-attribute/edit.html.twig', [
            'form' => $form->createView(),
            'heading' => "Edit attributes for $supplier",
            'cancelUri' => $returnUri,
        ]);
    }

    /**
     * @return SupplierAttributeRepository
     */
    private function getAttributeRepo()
    {
        return $this->getRepository(SupplierAttribute::class);
    }
}
