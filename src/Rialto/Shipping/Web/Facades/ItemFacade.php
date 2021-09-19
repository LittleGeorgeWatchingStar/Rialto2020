<?php

namespace Rialto\Shipping\Web\Facades;

use Rialto\Purchasing\Producer\StockProducer;
use Twig\Environment;

class ItemFacade
{
    /** @var StockProducer */
    private $item;

    /** @var Environment */
    private $twig;

    public function __construct(StockProducer $item, Environment $twig)
    {
        $this->item = $item;
        $this->twig = $twig;
    }

    public function getHtml()
    {
        if ($this->item->isWorkOrder()) {
            $template = $this->twig->createTemplate('<div>{{ work_order_link(item, item.fullSku) }}</div>');
            return $template->render([
                'item' => $this->item
            ]);
        }
    }

    public function getQtyHtml()
    {
        if ($this->item->isWorkOrder()) {
            $template = $this->twig->createTemplate(
                '<div>{{ item.qtyOrdered | number_format }}</div>');
            return $template->render([
                'item' => $this->item
            ]);
        }
    }
}