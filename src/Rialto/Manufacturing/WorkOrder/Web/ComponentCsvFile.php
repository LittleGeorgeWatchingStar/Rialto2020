<?php

namespace Rialto\Manufacturing\WorkOrder\Web;


use Gumstix\Filetype\CsvFileWithHeadings;
use Rialto\Manufacturing\WorkOrder\WorkOrder;

final class ComponentCsvFile
{
    /** @return CsvFileWithHeadings */
    public static function create(WorkOrder $wo)
    {
        $rows = [];
        foreach ($wo->getRequirements() as $woReq) {
            $row = [
                'sku' => $woReq->getFullSku(),
                'description' => $woReq->getDescription(),
                'qtyOrdered' => $woReq->getTotalQtyOrdered(),
                'qtyNeeded' => $woReq->getTotalQtyUndelivered(),
            ];

            $allocs = $woReq->getAllocations();
            if (count($allocs) > 0) {
                foreach ($allocs as $alloc) {
                    $row['qtyAllocated'] = $alloc->getQtyAllocated();
                    $row['allocatedFrom'] = $alloc->getSourceDescription();
                    $row['manufacturer'] = $alloc->getSource()->getManufacturer();
                    $row['manufacturerCode'] = $alloc->getSource()->getManufacturerCode();
                    $rows[] = $row;
                    $row = [
                        'sku' => '',
                        'description' => '',
                        'qtyOrdered' => '',
                        'qtyNeeded' => '',
                    ];
                }
            } else {
                $row['qtyAllocated'] = 0;
                $row['allocatedFrom'] = '';
                $row['manufacturer'] = '';
                $row['manufacturerCode'] = '';
                $rows[] = $row;
            }
        }

        $csv = new CsvFileWithHeadings();
        $csv->useWindowsNewline();
        $csv->parseArray($rows);
        return $csv;
    }

    /** @return string */
    public static function getFilename(WorkOrder $wo)
    {
        $stockCode = $wo->getFullSku();
        $poId = $wo->getPurchaseOrderNumber();
        $woId = $wo->getId();
        $id = $poId ? "PO{$poId}" : "WO{$woId}";
        return "{$id}_{$stockCode}.csv";
    }
}
