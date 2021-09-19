<?php

namespace Rialto\Purchasing\Order\Web;


use Gumstix\Filetype\CsvFileWithHeadings;
use Rialto\Purchasing\Order\PurchaseOrder;

class PurchaseOrderCsv
{
    public static function create(PurchaseOrder $order)
    {
        $rows = [];
        foreach ($order->getLineItems() as $poItem) {
            /* TODO: make columns supplier-specific. */
            $rows[] = [
                'Digi-Key Part Number' => $poItem->getCatalogNumber(),
                'Manufacturer Name' => '',
                'Manufacturer Part Number' => $poItem->getManufacturerCode(),
                'Customer Reference' => $poItem->getSku(),
                'Quantity 1' => $poItem->getQtyOrdered(),
                'Quantity 2' => '',
                'Quantity 3' => '',
            ];
        }
        $csv = new CsvFileWithHeadings();
        $csv->parseArray($rows);
        return $csv;
    }

}
