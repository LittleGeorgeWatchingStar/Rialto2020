<?php


namespace Rialto\Accounting\Ledger\Entry\Web;


use Gumstix\Filetype\CsvFileWithHeadings;
use Rialto\Accounting\Ledger\Entry\GLEntry;

/**
 * Helper class for constructing a CSV file from a list of GLEntries
 */
final class GLEntryCsv
{
    const DATE_FORMAT = 'Y-m-d H:i:s';
    /**
     * @param GLEntry[] $entries
     * @return CsvFileWithHeadings
     */
    public static function create($entries): CsvFileWithHeadings
    {
        $rows = [];
        foreach ($entries as $entry) {
            $rows[] = [
                'id' => $entry->getId(),
                'source' => "{$entry->getSystemType()} {$entry->getSystemTypeNumber()}",
                'date' => $entry->getDate()->format(self::DATE_FORMAT),
                'amount' => $entry->getAmount(),
                'account' => $entry->getAccount(),
                'memo' => $entry->getNarrative(),
            ];
        }

        $csv = new CsvFileWithHeadings();
        $csv->parseArray($rows);
        return $csv;
    }
}
