<?php

namespace Rialto\Purchasing\Catalog\Web;

use Gumstix\Filetype\CsvFile;
use Gumstix\Filetype\CsvFileWithHeadings;
use Rialto\Purchasing\Catalog\PurchasingData;

class PurchasingDataCsv
{
    /**
     * @param PurchasingData[] $list
     * @return CsvFile
     */
    public static function generate($list)
    {
        $rows = [];
        foreach ($list as $pd) {
            $rows[] = [
                'id' => $pd->getId(),
                'sku' => $pd->getSku(),
                'version' => $pd->getVersion(),
                'supplier' => $pd->getSupplierName(),
                'catalogNo' => $pd->getCatalogNumber(),
                'manufacturer' => $pd->getManufacturer(),
                'manufacturerCode' => $pd->getManufacturerCode(),
                'preferred' => $pd->isPreferred() ? 'yes' : 'no',
                'min order qty' => $pd->getMinimumOrderQty(),
                'economic order qty' => $pd->getEconomicOrderQty(),
                'std cost' => $pd->getStandardCost(),
                'unit cost @ eoq' => $pd->getCostAtEoq(),
            ];
        }
        $csv = new CsvFileWithHeadings();
        $csv->useWindowsNewline();
        $csv->parseArray($rows);
        return $csv;
    }
}
