<?php

namespace Rialto\Purchasing\Web;

use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Supplier\Supplier;
use Symfony\Component\Routing\RouterInterface;

/**
 * Generate URLs for common purchasing entities.
 */
class PurchasingRouter
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function supplierView(Supplier $supplier)
    {
        return $this->router->generate('supplier_view', [
            'supplier' => $supplier->getId(),
        ]);
    }

    public function orderView(PurchaseOrder $order)
    {
        return $this->router->generate('purchase_order_view', [
            'order' => $order->getId(),
        ]);
    }

    public function purchasingDataEdit(PurchasingData $data)
    {
        return $this->router->generate('purchasing_data_edit', [
            'id' => $data->getId(),
        ]);
    }
}
