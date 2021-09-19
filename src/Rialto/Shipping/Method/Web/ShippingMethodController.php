<?php

namespace Rialto\Shipping\Method\Web;

use Rialto\Shipping\Method\ShippingMethod;
use Rialto\Shipping\Shipper\Shipper;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for managing which shipping methods are available.
 */
class ShippingMethodController extends RialtoController
{
    /**
     * @Route("/Shipping/Shipper/{id}/shippingMethod/",
     *   name="Shipping_ShippingMethod_create")
     * @Method({"GET", "POST"})
     * @Template("core/form/dialogForm.html.twig")
     */
    public function createAction(Shipper $shipper, Request $request)
    {
        $form = $this->createForm(CreateShippingMethodType::class, null, [
            'shipper' => $shipper,
        ]);
        $returnUri = $this->shipperUrl($shipper);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $method = $form->getData();
                assert($method instanceof ShippingMethod);
                $this->dbm->persist($method);
                $this->dbm->flush();

                return JsonResponse::javascriptRedirect($returnUri);
            } else {
                return JsonResponse::fromInvalidForm($form);
            }
        }

        return [
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
            'cancelUri' => $returnUri,
        ];
    }

    private function shipperUrl(Shipper $shipper)
    {
        return $this->generateUrl('shipper_view', [
            'id' => $shipper->getId(),
        ]);
    }

    /**
     * Edit an existing shipping method.
     * @Route("/Shipping/Shippper/{id}/shippingMethod/{code}/",
     *   name="Shipping_ShippingMethod_edit")
     * @Method({"GET", "POST"})
     * @Template("core/form/dialogForm.html.twig")
     */
    public function editAction(Shipper $shipper, $code, Request $request)
    {
        if (! $shipper->hasShippingMethod($code)) {
            throw $this->notFound();
        }
        $method = $shipper->getShippingMethod($code);
        $form = $this->createForm(EditShippingMethodType::class, $method);
        $returnUri = $this->shipperUrl($shipper);

        if ($request->isMethod('post')) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->dbm->flush();
                return JsonResponse::javascriptRedirect($returnUri);
            } else {
                return JsonResponse::fromInvalidForm($form);
            }
        }

        return [
            'form' => $form->createView(),
            'formAction' => $this->getCurrentUri(),
            'cancelUri' => $returnUri,
        ];
    }

}
