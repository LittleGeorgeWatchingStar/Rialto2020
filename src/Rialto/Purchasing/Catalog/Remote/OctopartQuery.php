<?php

namespace Rialto\Purchasing\Catalog\Remote;

use Rialto\Accounting\Currency\Currency;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Supplier\Supplier;
use Symfony\Component\Validator\Constraints as Assert;

class OctopartQuery
{
    /** @var Supplier */
    public $supplier = null;

    /** @var string */
    public $catalogNo = '';

    public $manufacturerCode = '';

    public $currency = Currency::USD;

    public static function fromPurchasingData(PurchasingData $pd)
    {
        $terms = new self();
        $terms->supplier = $pd->getSupplier();
        $terms->catalogNo = $pd->getCatalogNumber();
        $terms->manufacturerCode = $pd->getManufacturerCode();
        return $terms;
    }

    public function __toString()
    {
        return json_encode($this->getSearchTerms());
    }

    /**
     * @Assert\Count(min=1, minMessage="Please enter a search term.")
     */
    public function getSearchTerms()
    {
        return array_filter([
            'sku' => $this->catalogNo,
            'mpn' => $this->manufacturerCode,
        ]);
    }

    public function matches(array $item, array $offer)
    {
        $match = true;
        if ($this->currency) {
            $match = $match && $this->hasCurrency($offer);
        }
        if ($this->supplier) {
            $match = $match && $this->supplierMatches($offer);
        }
        if ($this->catalogNo) {
            $match = $match && $this->skuMatches($offer);
        }

        return $match;
    }

    private function hasCurrency(array $offer)
    {
        return isset($offer['prices'][$this->currency]);
    }

    private function supplierMatches(array $offer)
    {
        $homepage = strtolower($offer['seller']['homepage_url']);
        return is_substring($this->supplier->getDomainName(), $homepage);
    }

    private function skuMatches(array $offer)
    {
        return empty($offer['sku'])
            ? false
            : strtolower($offer['sku']) == strtolower($this->catalogNo);
    }
}
