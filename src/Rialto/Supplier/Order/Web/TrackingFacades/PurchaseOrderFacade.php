<?php

namespace Rialto\Supplier\Order\Web\TrackingFacades;

use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Producer\StockProducer;
use Twig\Environment;

class PurchaseOrderFacade
{
    /** @var PurchaseOrder */
    private $purchaseOrder;

    /** @var StockProducer[] */
    private $items = [];

    /** @var Environment */
    private $twig;

    public function __construct(PurchaseOrder $purchaseOrder, Environment $twig)
    {
        $this->purchaseOrder = $purchaseOrder;

        $this->twig = $twig;

        $this->items = $purchaseOrder->getLineItems();
    }

    public function getPurchaseOrderNumberHtml()
    {
        $template = $this->twig->createTemplate('{{ purchase_order_link(order) }}');
        return $template->render([
            'order'=> $this->purchaseOrder
        ]);
    }

    public function getItems()
    {
        $usefulItems = array_values(array_filter($this->items, function (StockProducer $item) {
            if ($item->getQtyRemaining() > 0){
                return $item;
            }
        }));

        return array_map(function (StockProducer $item) {
            return new ItemFacade($item, $this->twig);
        }, $usefulItems);
    }

    public function getID()
    {
        return $this->purchaseOrder->getId();
    }

    public function getSupplierHtml()
    {
        if ($this->purchaseOrder->hasSupplier()) {
            $template = $this->twig->createTemplate('{{ truncated_supplier_link(supplier) }}');
            return $template->render([
                'supplier'=> $this->purchaseOrder->getSupplier()
            ]);
        } else {
            return null;
        }
    }

    public function getReceiveLink()
    {
        $template = $this->twig->createTemplate('{{ path(\'supplier_incoming_receive\', {id: order.id}) }}');
        return $template->render([
            'order'=> $this->purchaseOrder
        ]);

    }
}