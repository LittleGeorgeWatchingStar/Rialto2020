<?php

namespace Rialto\Accounting\Report;


use Gumstix\Filetype\CsvFile;
use Rialto\Accounting\Money;

class BalanceSheetCsv extends CsvFile
{
    public function __construct(BalanceSheet $sheet)
    {
        /* Column headings */
        $headings = ['Code', 'Account'];
        foreach ( $sheet->getPeriods() as $period ) {
            $headings[] = $period->formatEndDate('Y-m-d');
        }

        /* Row data */
        $data = [$headings];
        foreach ( $sheet->getSections() as $section => $groups ) {
            $data[] = ["== $section =="];
            foreach ( $groups as $group => $accounts ) {
                $data[] = ["- $group - "];
                foreach ( $accounts as $accountID => $balances ) {
                    $account = $sheet->getAccount($accountID);
                    $row = [
                        'code' => $accountID,
                        'account' => $account->getName()
                    ];
                    foreach ( $sheet->getPeriods() as $period ) {
                        $balance = $balances[$period->getId()];
                        $endDate = $period->formatEndDate('Y-m-d');
                        $row[$endDate] = $balance->getBalanceToDateForReporting();
                    }
                    $data[] = $row;
                }
            }
            $total = ["TOTAL $section", ''];
            foreach ( $sheet->getPeriods() as $period ) {
                $total[] = $sheet->getSectionTotal($section, $period);
            }
            $data[] = $total;
            $data[] = []; // blank line
        }
        $profitLoss = ['P&L total', ''];
        $check = ['Check total', ''];
        foreach ( $sheet->getPeriods() as $period ) {
            $profitLoss[] = $sheet->getProfitAndLossTotal($period);
            $check[] = Money::round($sheet->getPeriodTotal($period));
        }
        $data[] = $profitLoss;
        $data[] = $check;

        $this->parseArray($data);
    }
}
