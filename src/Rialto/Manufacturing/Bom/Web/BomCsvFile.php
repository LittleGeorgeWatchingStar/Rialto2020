<?php

namespace Rialto\Manufacturing\Bom\Web;

use Gumstix\Filetype\CsvFileWithHeadings;
use Rialto\Manufacturing\Component\Component;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Rialto\Web\Form\ArrayToCommaDelimitedStringTransformer;
use Symfony\Component\HttpFoundation\File\File;

/**
 * A .csv file that contains the Bill of Materials (BOM) for a
 * manufactured item.
 */
class BomCsvFile extends CsvFileWithHeadings
{
    const HEADING_SKU = 'sku';
    const HEADING_NAME = 'description';
    const HEADING_QTY = 'unitqty';
    const HEADING_PANEL_QTY = 'panelqty';
    const HEADING_MPN = 'manufacturercode';
    const HEADING_AML = 'alternativemanufacturercode';
    const HEADING_MANUFACTURER_NAME = 'manufacturername';
    const HEADING_SUPPLIER_NAME = 'suppliername';
    const HEADING_DISTRIBUTOR_NUMBER = 'distributornumber';
    const HEADING_PACKAGE = 'device';
    const HEADING_PARTVALUE = 'value';
    const HEADING_DESIGNATORS = 'designators';
    const HEADING_WORKTYPE = 'worktype';

    public static function getRequiredHeadings()
    {
        return [
            self::HEADING_SKU,
            self::HEADING_QTY,
            self::HEADING_WORKTYPE,
        ];
    }

    public static function getOptionalHeadings()
    {
        return [
            self::HEADING_MPN,
            self::HEADING_PACKAGE,
            self::HEADING_PARTVALUE,
            self::HEADING_DESIGNATORS,
        ];
    }

    /**
     * Factory function.
     *
     * @param Component[] $components
     * @param PurchasingDataRepository|null $repo
     * @param bool $isPanel
     * @return BomCsvFile
     */
    public static function fromComponents(array $components,
                                          ?PurchasingDataRepository $repo = null, bool $isPanel = false)
    {
        $data = [];
        foreach ($components as $component) {
            $data[] = self::convertComponent($component, $isPanel, $repo);
        }
        $csvFile = new self();
        $csvFile->parseArray($data);
        return $csvFile;
    }

    private static function convertComponent(Component $component, bool $isPanel,
                                             ?PurchasingDataRepository $repo = null)
    {
        if ($repo != null) {
            $stockItem = $component->getStockItem();
            $itemVersion = $stockItem->getVersion($component->getVersion());
            $purchData = $repo->findPreferred($itemVersion);
            $manufacturer = $purchData ? $purchData->getManufacturer() : null;
            $supplier = $purchData ? $purchData->getSupplier() : null;
            $catalogNumber = $purchData ? $purchData->getCatalogNumber() : null;

            return [
                self::HEADING_SKU => $component->getSku(),
                self::HEADING_NAME => $component->getDescription(),
                self::HEADING_QTY => $component->getUnitQty(),
                self::HEADING_MPN => $component->getManufacturerCode(),
                self::HEADING_AML => join(', ', self::getPurchasingDataList($component->getSku(), $component->getManufacturerCode(), $repo)),
                self::HEADING_MANUFACTURER_NAME => $manufacturer ? $manufacturer->getName() : null,
                self::HEADING_SUPPLIER_NAME => $supplier ? $supplier->getName() : '',
                self::HEADING_DISTRIBUTOR_NUMBER => $catalogNumber,
                self::HEADING_PACKAGE => $component->getPackage(),
                self::HEADING_PARTVALUE => $component->getPartValue(),
                self::HEADING_WORKTYPE => $component->getWorkType()->getId(),
                self::HEADING_DESIGNATORS => join(', ', $component->getDesignators()),
            ];
        } else {
            return [
                self::HEADING_SKU => $component->getSku(),
                self::HEADING_NAME => $component->getDescription(),
                self::getQtyHeading($isPanel) => $component->getUnitQty(),
                self::HEADING_MPN => $component->getManufacturerCode(),
                self::HEADING_PACKAGE => $component->getPackage(),
                self::HEADING_PARTVALUE => $component->getPartValue(),
                self::HEADING_WORKTYPE => $component->getWorkType()->getId(),
                self::HEADING_DESIGNATORS => join(', ', $component->getDesignators()),
            ];
        }
    }

    private static function getQtyHeading(bool $isPanel)
    {
        if ($isPanel === true) {
            return self::HEADING_PANEL_QTY;
        } else {
            return self::HEADING_QTY;
        }
    }

    public static function fromUploadedFile(File $file)
    {
        $csvFile = new self();
        $csvFile->parseFile($file);
        return $csvFile;
    }

    protected function validateHeadings(array $headings)
    {
        return array_map('strtolower', $headings);
    }

    /**
     *  @return string[]
     */
    public static function getPurchasingDataList(string $sku,
                                                 string $preferredMPN,
                                                 PurchasingDataRepository $repo)
    {
        $pdArray = $repo->findAllPurchasingDataBySku($sku);

        $originalMPNArray = array_filter(array_map(function(PurchasingData $pd) {
            if ($pd->getManufacturerCode() != null){
                return $pd->getManufacturerCode();
            } else {
                // return empty string,
                // which will be taken care of by array_filter
                return "";
            }
        }, $pdArray));

        // return array with preferred MPN trimmed
        return array_filter($originalMPNArray, function($k) use ($preferredMPN) {
            return $preferredMPN !== $k;
        });
    }

    public function getLines()
    {
        return $this->lines;
    }

    public function getStockCode(array $row)
    {
        return $row[self::HEADING_SKU];
    }

    /** @return Version|null */
    public function getComponentVersion(array $row, StockItem $component)
    {
        if (!$component->isVersioned()) return null;
        if (!$component->isPCB()) return null;

        $value = $row[self::HEADING_PARTVALUE];
        if ($component->hasVersion($value)) {
            return $component->getVersion($value);
        }

        $matches = [];
        if (preg_match('/^R(\d+)/', $value, $matches)) {
            $value = $matches[1];
            if ($component->hasVersion($value)) {
                return $component->getVersion($value);
            }
        }

        $matches = [];
        if (preg_match('/^R(\d+)/', $row[self::HEADING_PACKAGE], $matches)) {
            $value = $matches[1];
            if ($component->hasVersion($value)) {
                return $component->getVersion($value);
            }
        }

        return null;
    }

    public function getQuantity(array $row)
    {
        return $row[self::HEADING_QTY];
    }

    public function getDesignators(array $row)
    {
        $parts = $row[self::HEADING_DESIGNATORS];
        $transformer = new ArrayToCommaDelimitedStringTransformer();
        return $transformer->reverseTransform($parts);
    }
}
