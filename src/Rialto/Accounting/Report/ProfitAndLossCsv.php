<?php

namespace Rialto\Accounting\Report;


use Gumstix\Filetype\CsvFile;

class ProfitAndLossCsv extends CsvFile
{
    public function __construct(ProfitAndLossReport $report)
    {
        /* Column headings */
        $headings = ['Code', 'Account'];
        foreach ( $report->getColumns() as $column ) {
            $headings[] = $column->formatDates('Y-m-d');
        }

        /* Row data */
        $data = [$headings];
        foreach ( $report->getSections() as $sectionID => $groups ) {
            $sectionName = $report->getSectionName($sectionID);
            $data[] = ["== $sectionName =="];
            foreach ( $groups as $groupName => $accounts ) {
                $data[] = ["- $groupName - "];
                foreach ( $accounts as $accountID => $accountName ) {
                    $row = [$accountID, $accountName];
                    foreach ( $report->getColumns() as $column ) {
                        $row[] = $column->getAmount($accountID);
                    }
                    $data[] = $row;
                }
            }
            foreach ( $report->getSectionAnalysis($sectionID) as $name ) {
                $row = [$name, ''];
                foreach ( $report->getColumns() as $column ) {
                    $row[] = $column->getSectionAnalysis($name);
                }
                $data[] = $row;
            }
            $data[] = []; // blank line
        }

        $this->parseArray($data);
    }
}
