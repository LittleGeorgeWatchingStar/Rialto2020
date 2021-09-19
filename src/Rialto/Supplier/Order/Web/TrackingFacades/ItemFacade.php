<?php

namespace Rialto\Supplier\Order\Web\TrackingFacades;

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
        if ($this->item->isStockItem()) {
            $template = $this->twig->createTemplate(
                '<span>
                            {{ item_link(item) }}:
                            {{ item.qtyRemaining | number_format }} pcs
                            <span class="days-display{{ item.isOverdue ? \' overdue\' : \'\' }}">
                                {% if item.commitmentDate %}
                                    {{ item.commitmentDate | user_date }}
                                {% elseif item.requestedDate %}
                                    {{ item.requestedDate | user_date }}
                                {% else %}
                                     No Date Found
                                {% endif %}
                            </span>
                        </span>');
            return $template->render([
                'item' => $this->item
            ]);
        }
    }

    public function getPartNum()
    {
        if ($this->item->isStockItem()) {
            if ($this->item->getPurchasingData()) {
                $purchData = $this->item->getPurchasingData();
                if ($purchData->getManufacturerCode()) {
                    return "MPN:" . $purchData->getManufacturerCode();
                } else {
                    return "SPN:" . $purchData->getCatalogNumber();
                }
            } else {
                return "no part number";
            }
        }
    }
}