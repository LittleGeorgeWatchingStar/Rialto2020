<?php

namespace Rialto\Stock\Move\Web;


use Gumstix\Filetype\CsvFileWithHeadings;
use Rialto\Stock\Move\StockMove;

/**
 * Helper class for constructing a CSV file from a list of StockMoves
 */
final class StockMoveCsv
{
    const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @param StockMove[] $moves
     */
    public static function create(Iterable $moves): CsvFileWithHeadings
    {
        $rows = [];
        foreach ($moves as $move) {
            $bin = $move->getStockBin();
            $rows[] = [
                'id' => $move->getId(),
                'transaction' => $move->getSystemType()->getName() . ' ' . $move->getSystemTypeNumber(),
                'sku' => $move->getSku(),
                'location' => $move->getLocation()->getName(),
                'date' => $move->getDate()->format(self::DATE_FORMAT),
                'quantity' => $move->getQuantity(),
                'bin' => $bin ? $bin->getId() : null,
                'reference' => $move->getReference(),
            ];
        }

        $csv = new CsvFileWithHeadings();
        $csv->parseArray($rows);
        return $csv;
    }
}
