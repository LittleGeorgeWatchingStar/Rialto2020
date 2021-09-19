<?php

namespace Rialto\Manufacturing\Kit\Email;


use Gumstix\Filetype\CsvFileWithHeadings;
use Rialto\Manufacturing\Kit\WorkOrderKit;
use Rialto\Stock\Bin\StockBin;

/**
 * Creates the CSV file for a work order kit.
 */
final class KitCsv
{
    public static function create(WorkOrderKit $kit): CsvFileWithHeadings
    {
        $rows = [];
        foreach ($kit->getRequirements() as $kitReq) {
            $row = [
                'stockCode' => $kitReq->getSku(),
                'version' => $kitReq->getVersion(),
                'description' => $kitReq->getDescription(),
                'grossNeeded' => $kitReq->getGrossQtyNeeded(),
                'previouslySent' => $kitReq->getQtyAllocatedAtDestination(),
                'shortages' => $kitReq->getQtyUnallocated(),
                'manufacturerCode' => $kitReq->getManufacturerCode(),
                'qtySent' => 0,
            ];

            $bins = [];
            foreach ($kitReq->getAllocationGroupsAtOrigin() as $group) {
                $source = $group->getSource();
                if ($source instanceof StockBin) {
                    $bins[] = $source->getId();
                    $row['qtySent'] += $source->getQtyRemaining();
                }
            }
            $row['binsSent'] = join(',', $bins);
            if ($row['qtySent'] > 0){
                $rows[] = $row;
            }
        }

        usort($rows, function (array $row1, array $row2) {
            $isSent1 = ($row1['qtySent'] > 0);
            $isSent2 = ($row2['qtySent'] > 0);
            if ($isSent1 == $isSent2) {
                return $row1['stockCode'] > $row2['stockCode'];
            }
            return $isSent2 - $isSent1;
        });

        $file = new CsvFileWithHeadings();
        $file->parseArray($rows);
        return $file;
    }
}
