<?php

namespace Rialto\Shipping\Export\Web;

use Rialto\Security\Role\Role;
use Rialto\Shipping\Export\ShipmentProhibition;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for managing shipping prohibitions.
 *
 * @see ShippingProhibition
 */
class ShipmentProhibitionController extends RialtoController
{
    /**
     * @Route("/shipping/prohibition/", name="shipment_prohibition_list")
     * @Method("GET")
     * @Template("shipping/prohibition/list.html.twig")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $prohibs = $this->getRepository(ShipmentProhibition::class)
            ->findAll();

        return [
            'list' => $prohibs,
        ];
    }

    /**
     * @Route("/Shipping/ShipmentProhibition",
     *     name="Shipping_ShipmentProhibition_create")
     * @Template("shipping/prohibition/edit.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $prohib = new ShipmentProhibition();
        return $this->processForm($prohib, 'create', $request);
    }

    /**
     * @Route("/Shipping/ShipmentProhibition/{id}",
     *     name="Shipping_ShipmentProhibition_edit")
     * @Template("shipping/prohibition/edit.html.twig")
     */
    public function editAction(ShipmentProhibition $prohib, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        return $this->processForm($prohib, 'update', $request);
    }

    private function processForm(ShipmentProhibition $prohib, $update, Request $request)
    {
        $form = $this->createForm(ShipmentProhibitionType::class, $prohib);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->persist($prohib);
            $this->dbm->flush();
            $this->logNotice("Shipping prohibition {$update}d successfully.");
            return $this->redirect($this->getListUri());
        }

        return [
            'form' => $form->createView(),
            'cancelUri' => $this->getListUri(),
            'update' => $update,
            'prohib' => $prohib,
        ];
    }

    private function getListUri()
    {
        return $this->generateUrl('shipment_prohibition_list');
    }

    /**
         * @Route("/record/Shipping/ShipmentProhibition/{id}",
     *     name="Shipping_ShipmentProhibition_delete")
     * @Method("DELETE")
     */
    public function deleteAction(ShipmentProhibition $prohib)
    {
        $this->denyAccessUnlessGranted(Role::STOCK);
        $this->dbm->remove($prohib);
        $this->dbm->flush();
        $this->logNotice("Shipping prohibition deleted successfully.");
        return $this->redirect($this->getListUri());
    }
}
