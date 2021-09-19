<?php

namespace Rialto\Madison\Feature\Web;

use Rialto\Madison\Feature\StockItemFeature;
use Rialto\Web\Serializer\ListableFacade;

class FeatureSummary
{
    use ListableFacade;

    /** @var StockItemFeature */
    private $feature;

    public function __construct(StockItemFeature $feature)
    {
        $this->feature = $feature;
    }

    public function getFeatureCode()
    {
        return $this->feature->getFeatureCode();
    }

    public function getValue()
    {
        return $this->feature->getValue();
    }

    public function getDetails()
    {
        return $this->feature->getDetails();
    }
}
