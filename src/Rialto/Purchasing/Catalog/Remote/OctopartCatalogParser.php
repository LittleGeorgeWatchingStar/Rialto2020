<?php

namespace Rialto\Purchasing\Catalog\Remote;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Measurement\Temperature\TemperatureRange;
use Rialto\Purchasing\Catalog\CatalogItem;
use Rialto\Purchasing\Catalog\CostBreak;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Manufacturer\Manufacturer;
use Rialto\Purchasing\Manufacturer\Orm\ManufacturerRepository;
use Rialto\Purchasing\Supplier\Orm\SupplierRepository;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Bin\BinStyle;
use Rialto\Stock\Bin\BinStyleRepo;
use Rialto\Stock\Category\StockCategory;
use Rialto\Stock\Item\PurchasedStockItem;
use Rialto\Stock\Item\Version\Version;
use Rialto\Tax\Authority\TaxAuthority;

class OctopartCatalogParser
{
    /** @var ObjectManager */
    private $om;

    /**
     * This is used to prevent creating duplicate manufacturers.
     *
     * @var Manufacturer[] indexed by name
     */
    private $manufacturers = [];

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * @param string $json The JSON response body from Octopart
     * @param PurchasingData $supplierName
     * @return CatalogItem
     * @throws OctopartCatalogException If a matching entry cannot be found
     */
    public function findMatchingEntry($json, PurchasingData $purchData)
    {
        $query = OctopartQuery::fromPurchasingData($purchData);
        $data = $this->decodeResults($json);
        foreach ($data as $resultData) {
            foreach ($resultData['items'] as $itemData) {
                foreach ($itemData['offers'] as $offer) {
                    if ($query->matches($itemData, $offer)) {
                        $pd = $this->createPurchData(
                            $purchData->getStockItem(),
                            $itemData,
                            $offer,
                            $query->currency);
                        if ($pd) {
                            return $pd;
                        }
                    }
                }
            }
        }
        $suppName = $purchData->getSupplierName();
        $catNumber = $purchData->getCatalogNumber();
        $msg = "Octopart has no result that matches $suppName and $catNumber";
        throw new OctopartCatalogException($msg);
    }

    /**
     * @param string $json The JSON response body from Octopart
     * @return CatalogResult[]
     */
    public function findMatchingItems($json, OctopartQuery $query, ?PurchasedStockItem $item = null)
    {
        $data = $this->decodeResults($json);
        $results = [];
        foreach ($data as $resultData) {
            foreach ($resultData['items'] as $itemData) {
                if ($item === null) {
                    $result = $this->createResult($itemData);
                } else {
                    $result = new CatalogResult($item);
                }
                foreach ($itemData['offers'] as $offer) {
                    if ($query->matches($itemData, $offer)) {
                        $pd = $this->createPurchData(
                            $result->getItem(),
                            $itemData,
                            $offer,
                            $query->currency);
                        $result->addPurchData($pd);
                    }
                }
                if ($result->hasPurchData()) {
                    $results[] = $result;
                }
            }
        }
        return $results;
    }

    private function decodeResults($json)
    {
        $data = json_decode($json, true);
        return $data['results'];
    }

    /** @return CatalogResult */
    private function createResult(array $itemdata)
    {
        $item = new PurchasedStockItem();
        $item->setCategory(StockCategory::fetchPart($this->om));
        $item->setName($itemdata['short_description']);
        $item->setLongDescription($itemdata['short_description']);
        $specs = $this->parseSpecs($itemdata);
        $item->setPackage($this->getSpec($specs, 'case_package'));
        $item->setPartValue($this->findPartValue($specs));
        $item->setRoHS($this->getSpec($specs, 'rohs_status'));
        $item->setTaxAuthority(TaxAuthority::fetchCaStateTax($this->om));
        $item->setTemperatureRange($this->getTemperatureRange($itemdata));
        $item->addVersion(Version::NONE);

        return new CatalogResult($item);
    }

    private function getTemperatureRange(array $itemdata)
    {
        if (isset($itemdata['specs']['operating_temperature'])) {
            $temp = $itemdata['specs']['operating_temperature'];
            return new TemperatureRange(
                isset($temp['min_value']) ? $temp['min_value'] : null,
                isset($temp['max_value']) ? $temp['max_value'] : null);
        }
        return null;
    }

    private function createPurchData(PurchasedStockItem $item,
                                     array $itemData,
                                     array $offer,
                                     $currencyCode)
    {
        $entry = new PurchasingData($item);
        $supplier = $this->findSupplier($offer['seller']);
        if (!$supplier) {
            return null;
        }
        $entry->setSupplier($supplier);
        $entry->setCatalogNumber($offer['sku']);
        $entry->setManufacturer($this->findManufacturer($itemData['manufacturer']));
        $entry->setManufacturerCode($itemData['mpn']);

        $entry->setQtyAvailable($offer['in_stock_quantity']);
        $entry->setIncrementQty($offer['order_multiple'] ?: 1);
        $entry->setProductUrl($offer['product_url']);
        $entry->setBinStyle($this->findBinStyle($offer['packaging']));

        $prices = $offer['prices'][$currencyCode];
        $entry->setCostBreaks($this->createCostBreaks($prices));
        $entry->setManufacturerLeadTime($offer['factory_lead_days'] ?? 0);
        $entry->setBinSize($this->determineBinSize($prices, $offer['order_multiple']));

        return $entry;
    }

    /** @return Supplier|null */
    private function findSupplier(array $seller)
    {
        /** @var $repo SupplierRepository */
        $repo = $this->om->getRepository(Supplier::class);
        return $repo->findFirstMatching($seller['name'], $seller['homepage_url']);
    }

    private function findManufacturer(array $manData)
    {
        $name = $manData['name'];
        if (! isset($this->manufacturers[$name])) {
            /** @var $repo ManufacturerRepository */
            $repo = $this->om->getRepository(Manufacturer::class);
            $this->manufacturers[$name] = $repo->findByNameOrCreate($name);
        }
        return $this->manufacturers[$name];
    }

    /** @return BinStyle */
    private function findBinStyle($style)
    {
        /** @var $repo BinStyleRepo */
        $repo = $this->om->getRepository(BinStyle::class);
        return $repo->findMatching($style);
    }

    private function createCostBreaks($prices)
    {
        $costBreaks = [];
        foreach ($prices as $price) {
            $costBreak = new CostBreak();
            $costBreak->setMinimumOrderQty($price[0]);
            $costBreak->setUnitCost($price[1]);
            $costBreaks[] = $costBreak;
        }
        return $costBreaks;
    }

    private function parseSpecs(array $item)
    {
        $specs = [];
        foreach ($item['specs'] as $name => $data) {
            $specs[$name] = isset($data['display_value'])
                ? $data['display_value']
                : reset($data['value']);
        }
        return $specs;
    }

    private function getSpec(array $specs, $name)
    {
        return isset($specs[$name]) ? $specs[$name] : null;
    }

    private function findPartValue(array $specs)
    {
        $fields = ['capacitance', 'resistance'];
        foreach ($fields as $field) {
            if (!empty($specs[$field])) {
                return $specs[$field];
            }
        }
        return null;
    }

    private function determineBinSize(array $prices, $orderMult)
    {
        if ($orderMult) {
            return $orderMult;
        }
        $gcd = $prices[0][0];
        for ($i = 1; $i < count($prices); $i++) {
            $break = $prices[$i];
            $gcd = gmp_gcd($gcd, $break[0]);
        }
        return gmp_intval($gcd);
    }
}
