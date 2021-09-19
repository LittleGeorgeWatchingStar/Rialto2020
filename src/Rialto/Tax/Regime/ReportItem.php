<?php

namespace Rialto\Tax\Regime;

class ReportItem
{
    private $regimes = [];
    public $regimeCode;
    public $county;
    public $city = '';
    public $taxRate = 0;
    public $sales = 0;
    public $taxesPaid = 0;
    public $taxesOwed = 0;

    public function addRegime(TaxRegime $regime)
    {
        /* Don't include the base tax rate */
        if ($regime->getCounty() == '') return;

        $id = $regime->getId();
        if (isset($this->regimes[$id])) return;

        $this->regimes[$id] = $regime;
        $this->regimeCode = $regime->getRegimeCode();
        $this->county = $regime->getCounty();
        $this->city = $regime->getCity();
        $this->taxRate += $regime->getTaxRate();
    }
}
