<?php

namespace Rialto\Stock\Transfer\Web;


use Gumstix\Filetype\CsvFileWithHeadings;

final class TransferReceiptCsv
{
    public static function create(TransferReceipt $receipt): CsvFileWithHeadings
    {
        $rows = [];
        foreach ($receipt->getItems() as $item) {
            $bin = $item->getStockBin();
            $rows[] = [
                'item' => $item->getFullSku(),
                'description' => $item->getDescription(),
                'binId' => $bin->getId(),
                'binQty' => $bin->getQuantity(),
                'mpn' => $bin->getManufacturerCode(),
                'qtySent' => $item->getQtySent(),
            ];
        }

        $csv = new CsvFileWithHeadings();
        $csv->parseArray($rows);
        return $csv;
    }
}
