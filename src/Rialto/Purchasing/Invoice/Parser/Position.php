<?php

namespace Rialto\Purchasing\Invoice\Parser;

use JMS\Serializer\Annotation\Type;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A position in a CSV document, relative to some text string, where
 * a field value might be found.
 */
class Position
{
    /**
     * @Type("integer")
     * @Assert\NotBlank
     * @Assert\Type(type="numeric")
     */
    public $x;

    /**
     * @Type("integer")
     * @Assert\Type(type="numeric")
     * @Assert\GreaterThanOrEqual(0)
     */
    public $deltaX = 0;

    /**
     * @Type("integer")
     * @Assert\NotBlank
     * @Assert\Type(type="numeric")
     */
    public $y;

    public function __construct($x, $y, $deltaX = 0)
    {
        $this->x = $x;
        $this->y = $y;
        $this->deltaX = $deltaX;
    }
}