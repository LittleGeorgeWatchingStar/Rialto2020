<?php

namespace Rialto\Sales\Order;


interface TaxableOrder
{
    /**
     * @return TaxableOrderItem[]
     */
    public function getLineItems();

    /**
     * @return float
     */
    public function getShippingPrice();
}
