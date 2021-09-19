<?php

namespace Rialto\Purchasing\Receiving\Web;

use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 */
class ServiceReceived
extends ItemReceived
{
    /**
     * @var integer|float
     * @Assert\Type(type="numeric")
     * @Assert\Range(min=0)
     */
    public $qtyReceived = 0;

    public function getTotalReceived()
    {
        return $this->qtyReceived;
    }
}
