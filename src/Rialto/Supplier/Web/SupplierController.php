<?php

namespace Rialto\Supplier\Web;


use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Supplier\SupplierVoter;
use Rialto\Web\Response\JsonResponse;
use Rialto\Web\RialtoController;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Base class for controllers that suppliers are allowed to use.
 */
abstract class SupplierController extends RialtoController
{
    /**
     * See if the current user is allowed access to the dashboard for
     * $supplier.
     */
    protected function checkDashboardAccess(Supplier $supplier)
    {
        $this->denyAccessUnlessGranted(SupplierVoter::DASHBOARD, $supplier);
    }

    protected function dialogRedirect(Supplier $supplier)
    {
        $uri = $this->getDashboardUri($supplier);
        $stack = $this->get(RequestStack::class); /* @var $stack RequestStack */
        $request = $stack->getCurrentRequest();
        return $request->isXmlHttpRequest() ?
            JsonResponse::javascriptRedirect($uri) :
            $this->redirect($uri);
    }

    protected function getDashboardUri(Supplier $supplier)
    {
        $default = $this->getOrderListUri($supplier);
        return $this->getReturnUri($default);
    }

    protected function getOrderListUri(Supplier $supplier)
    {
        return $this->generateUrl('supplier_order_list', [
            'id' => $supplier->getId(),
        ]);
    }
}
