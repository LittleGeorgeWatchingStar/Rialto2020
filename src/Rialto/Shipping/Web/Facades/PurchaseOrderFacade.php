<?php

namespace Rialto\Shipping\Web\Facades;

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
        return array_map(function (StockProducer $item) {
            return new ItemFacade($item, $this->twig);
        }, $this->items);
    }

    public function getID()
    {
        return $this->purchaseOrder->getId();
    }
}