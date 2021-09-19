<?php

namespace Rialto\Purchasing\Receiving\Web;

use Rialto\Stock\Bin\BinStyle;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A single bin that was received as part of a PO.
 */
class BinReceived
{
    /**
     * @var integer|float
     * @Assert\NotBlank(message="Quantity is required; if you did not receive any, enter 0.")
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    public $qtyReceived;

    /**
     * @var BinStyle
     * @Assert\NotNull
     */
    public $binStyle;

    public function __construct(BinStyle $binStyle = null, $qtyReceived = 0)
    {
        $this->binStyle = $binStyle;
        $this->qtyReceived = $qtyReceived;
    }
}
