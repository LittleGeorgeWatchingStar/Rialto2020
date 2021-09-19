<?php

namespace Rialto\Stock\Bin\Web;


use Rialto\Stock\Bin\BinStyle;
use Rialto\Web\Serializer\ListableFacade;

class BinStyleDetail
{
    use ListableFacade;

    /** @var BinStyle */
    private $style;

    public function __construct(BinStyle $style)
    {
        $this->style = $style;
    }

    public function getId()
    {
        return $this->style->getId();
    }

    public function getName()
    {
        return $this->getId(); // for backward-compatiblity
    }
}
