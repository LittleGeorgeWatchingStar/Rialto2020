<?php

namespace Rialto\Manufacturing\Customization\Web;

use Rialto\Database\Orm\EntityList;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\Customization\Orm\CustomizationRepository;
use Rialto\Security\Role\Role;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;


class CustomizationController extends RialtoController
{
    /**
     * @Route("/manufacturing/customizations/", name="customization_list")
     * @Method("GET")
     * @Template("manufacturing/customization/customization-list.html.twig")
     */
    public function listAction(Request $request)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::MANUFACTURING]);
        $form = $this->createForm(CustomizationListFilterType::class);
        $form->submit($request->query->all());
        $filters = $form->getData();
        $repo = $this->getRepository(Customization::class);
        $list = new EntityList($repo, $filters);
        return [
            'list' => $list,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/manufacturing/create-customization/",
     *   name="customization_create")
     * @Method({"GET", "POST"})
     * @Template("manufacturing/customization/customization-edit.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::MANUFACTURING]);
        $cmz = new Customization();
        $cmz->setStockCodePattern($request->get('sku'));
        return $this->processForm($cmz, 'created', $request);
    }

    /**
     * @Route("/manufacturing/customization/{id}/",
     *   name="customization_edit")
     * @Method({"GET", "POST"})
     * @Template("manufacturing/customization/customization-edit.html.twig")
     */
    public function editAction(Customization $cmz, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::MANUFACTURING]);
        return $this->processForm($cmz, 'updated', $request);
    }

    private function processForm(Customization $cmz, $updated, Request $request)
    {
        $form = $this->createForm(CustomizationType::class, $cmz);
        $returnUri = $this->generateUrl('customization_list', $request->query->all());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->persist($cmz);
            $this->dbm->flush();
            $id = $cmz->getId();
            $this->logNotice("Customization $id has been $updated successfully.");
            return $this->redirect($returnUri);
        }

        return [
            'form' => $form->createView(),
            'cmz' => $cmz,
            'cancelUri' => $returnUri,
        ];
    }

    /**
     * @Route("/manufacturing/customization/{id}/",
     *   name="customization_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Customization $cmz, Request $request)
    {
        $this->denyAccessUnlessGranted([Role::STOCK, Role::MANUFACTURING]);
        /** @var $repo CustomizationRepository */
        $repo = $this->getRepository(Customization::class);
        if ($repo->isUsed($cmz)) {
            $msg = "$cmz has been used and cannot be deleted.";
            $this->logError($msg);
        } else {
            $this->dbm->remove($cmz);
            $this->dbm->flush();
            $this->logNotice("$cmz deleted successfully.");
        }

        return $this->redirectToRoute('customization_list', $request->query->all());
    }
}
