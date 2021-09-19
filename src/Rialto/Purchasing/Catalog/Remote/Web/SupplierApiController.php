<?php

namespace Rialto\Purchasing\Catalog\Remote\Web;

use Rialto\Purchasing\Catalog\Remote\Orm\SupplierApiRepository;
use Rialto\Purchasing\Catalog\Remote\SupplierApi;
use Rialto\Purchasing\Catalog\Remote\SupplierApiList;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for maintaining and accessing supplier's catalog APIs.
 */
class SupplierApiController extends RialtoController
{
    /**
     * @Route("/Purchasing/SupplierApi", name="Purchasing_SupplierApi_edit")
     * @Template("purchasing/supplierApi/supplierApi-edit.html.twig")
     */
    public function editAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        /** @var $repo SupplierApiRepository */
        $repo = $this->dbm->getRepository(SupplierApi::class);
        $list = new SupplierApiList($repo->findAll());

        $form = $this->createFormBuilder($list)
            ->add('apis', CollectionType::class, [
                'entry_type' => SupplierApiType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'label' => false,
            ])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->beginTransaction();
            try {
                $list->persistAll($this->dbm);
                $this->dbm->flushAndCommit();
                $this->logNotice('Supplier API configuration updated successfully.');
                return $this->redirect($this->getCurrentUri());
            } catch (\Exception $ex) {
                $this->dbm->rollBack();
                throw $ex;
            }
        }

        return [
            'form' => $form->createView(),
        ];
    }

}
