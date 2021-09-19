<?php

namespace Rialto\Supplier\Order\Web\Facades;


use Rialto\Manufacturing\ClearToBuild\ClearToBuildEstimate;
use Rialto\Util\Date\Date;
use Twig\Environment;

class ClearToBuildFacade
{
    /** @var ClearToBuildEstimate */
    private $cTB;

    public function __construct(ClearToBuildEstimate $clearToBuildEstimate)
    {
        $this->cTB = $clearToBuildEstimate;
    }

    public function getEstimate()
    {
        $estimate = $this->cTB->getEstimateDate();
        return Date::toIso($estimate);
    }

    public function getAvailable()
    {
        return $this->cTB->isAvailable();
    }
}