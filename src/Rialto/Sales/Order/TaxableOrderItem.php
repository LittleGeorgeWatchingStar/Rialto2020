<?php


namespace Rialto\Sales\Order;


interface TaxableOrderItem
{
    /**
     * @return float
     */
    public function getTaxRate();

    /**
     * @return float
     */
    public function getExtendedPrice();
}
