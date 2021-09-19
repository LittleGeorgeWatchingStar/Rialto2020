<?php

namespace Rialto\Allocation\Web;

use Rialto\Purchasing\Producer\StockProducer;

/**
 * Special case of ConsumerStatusLine for stock that is on order.
 */
class ConsumerOrderStatus extends ConsumerStatusLine
{
    /**
     * @param StockProducer[] $producers
     */
    public function __construct($qty, array $producers, $icon)
    {
        $text = $this->getText($producers);
        parent::__construct($qty, $icon, $text);
    }

    /**
     * @param StockProducer[] $producers
     */
    private function getText(array $producers)
    {
        $parts = [];
        foreach ($producers as $prod) {
            $parts[] = ' PO#' . $prod->getOrderNumber();
        }
        return 'on ' . join(', ', $parts);
    }

}
