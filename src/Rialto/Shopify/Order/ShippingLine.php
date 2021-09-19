<?php

namespace Rialto\Shopify\Order;

use JMS\Serializer\Annotation\Type;

/**
 * A shipping_line item from the Shopify order API.
 */
class ShippingLine
{
    /**
     * @var string
     * @Type("string")
     */
    public $code;

    /**
     * @var float
     * @Type("double")
     */
    public $price;

    /**
     * @var string
     * @Type("string")
     */
    public $source;

//    public $tax_lines;

    /**
     * @var string
     * @Type("string")
     */
    public $title;
}
