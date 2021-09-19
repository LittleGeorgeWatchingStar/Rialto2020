<?php

namespace Rialto\Shipping\Shipper\Web;

use FOS\RestBundle\View\View;
use Rialto\Security\Role\Role;
use Rialto\Shipping\Shipper\Shipper;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for managing shippers such as UPS and FedEx.
 */
class ShipperController extends RialtoController
{
    /**
     * @Route("/shipping/shipper/", name="shipper_list")
     * @Method("GET")
     * @Template("shipping/shipper/shipper-list.html.twig")
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $shippers = $this->getRepository(Shipper::class)
            ->findAll();
        return ['shippers' => $shippers];
    }

    /**
     * @Route("/shipping/shipper/{id}/", name="shipper_view")
     * @Method("GET")
     * @Template("shipping/shipper/shipper-view.html.twig")
     */
    public function viewAction(Shipper $shipper)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        return ['entity' => $shipper];
    }

    /**
     * Create a new shipper.
     *
     * @Route("/Shipping/Shipper", name="Shipping_Shipper_create")
     * @Template("shipping/shipper/shipper-edit.html.twig")
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $shipper = new Shipper();
        return $this->renderForm($shipper, $request, 'Create a new shipper');
    }

    /**
     * Edit a shipper.
     *
     * @Route("/Shipping/Shipper/{id}", name="Shipping_Shipper_edit")
     * @Template("shipping/shipper/shipper-edit.html.twig")
     */
    public function editAction(Shipper $shipper, Request $request)
    {
        $this->denyAccessUnlessGranted(Role::ADMIN);
        $heading = "Edit shipper: $shipper";
        return $this->renderForm($shipper, $request, $heading);
    }

    private function renderForm(Shipper $shipper, Request $request, $heading)
    {
        $form = $this->createForm(ShipperType::class, $shipper);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->dbm->persist($shipper);
            $this->dbm->flush();
            $this->logNotice("$shipper updated successfully.");
            return $this->redirect($this->viewUrl($shipper));
        }

        return [
            'form' => $form->createView(),
            'heading' => $heading,
            'cancelUri' => $this->listUrl(),
        ];
    }

    private function viewUrl(Shipper $shipper)
    {
        return $this->generateUrl('shipper_view', [
            'id' => $shipper->getId(),
        ]);
    }

    private function listUrl()
    {
        return $this->generateUrl('shipper_list');
    }

    /**
     * Returns the shipping methods available from the given shipper.
     *
     * @Route("/Shipping/ShippingMethods/{id}")
     * @Method("GET")
     */
    public function shippingMethodsAction(Shipper $shipper)
    {
        $this->denyAccessUnlessGranted(Role::EMPLOYEE);
        $methods = $shipper->getDefaultShippingMethods();
        $data = $this->normalizeShippingMethods($methods);
        return View::create($data);
    }

    private function normalizeShippingMethods(array $methods): array
    {
        $data = [];
        foreach ($methods as $method) {
            $data[] = [
                'code' => $method->getCode(),
                'name' => $method->getName(),
            ];
        }
        return $data;
    }
}
