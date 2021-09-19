<?php

namespace Rialto\PcbNg\Service;

use Gumstix\Filetype\CsvFileWithHeadings;
use Rialto\Stock\Item\Version\ItemVersion;

/**
 * Renames column headers in the locations CSV so that the PCB:NG API can parse the header names,
 * and also adds MPN if it does not exist.
 */
class LocationsConverter
{
    public function convert(string $csvDataString,
                            ItemVersion $board): string
    {
        $csv = new CsvFileWithHeadings();
        $csv->parseString($csvDataString);

        if (!$csv->hasHeading('MPN')) {
            $this->insertMpn($csv, $board);
        }

        $csv = $csv->remap([
            'name' => 'identifier',
            'x (mm)' => 'x (mm)',
            'y (mm)' => 'y (mm)',
            'side' => 'side',
            'rotation' => 'rotation',
            'MPN' => 'MPN',
        ]);
        return $csv->toString();
    }

    private function insertMpn(CsvFileWithHeadings $csv,
                               ItemVersion $board): void
    {
        $bomItems = $board->getBomItems();

        /**
         * [Designator][Package] => MPN
         * @var string[][] $mpnMap
         */
        $mpnMap = [];
        foreach ($bomItems as $bomItem) {
            foreach ($bomItem->getDesignators() as $designator) {
                $package = $bomItem->getPackage();
                $mpn = $bomItem->getManufacturerCode();

                $mpnMap[$designator][$package] = $mpn;
            }
        }

        $array = $csv->toArray();
        foreach ($array as &$row) {
            $designator = $row['name'];
            $package = $row['package'];
            $row['MPN'] = $mpnMap[$designator][$package] ?? '';
        }

        $csv->parseArray($array);
    }
}