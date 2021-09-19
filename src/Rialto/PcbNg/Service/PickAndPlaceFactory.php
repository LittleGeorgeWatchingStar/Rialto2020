<?php

namespace Rialto\PcbNg\Service;


use Gumstix\Filetype\CsvFileWithHeadings;
use Gumstix\Storage\FileStorage;
use Rialto\Manufacturing\BuildFiles\PcbBuildFiles;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Stock\Item\Version\ItemVersion;

final class PickAndPlaceFactory
{
    /** @var PurchasingDataRepository */
    private $purchDataRepo;

    /** @var FileStorage */
    private $fileStorage;

    public function __construct(PurchasingDataRepository $purchDataRepo,
                                FileStorage $fileStorage)
    {
        $this->purchDataRepo = $purchDataRepo;
        $this->fileStorage = $fileStorage;
    }

    public function generateForBoard(ItemVersion $board,
                                     bool $includeDnp = false): string
    {
        foreach ($board->getBomItems() as $bomItem) {
            if ($bomItem->getComponent()->isPCB()) {
                $buildFiles = PcbBuildFiles::create(
                    $bomItem->getStockItem(),
                    $bomItem->getVersion(),
                    $this->fileStorage);
                if (!$buildFiles->exists(PcbBuildFiles::XY)) {
                    throw new \InvalidArgumentException("\"{$board->getFullSku()}\" does not have XY data.");
                }
                $locationsCsvData = $buildFiles->getContents(PcbBuildFiles::XY);
                return $this->populateFromXy($board, $locationsCsvData, $includeDnp);
            }
        }

        throw new \InvalidArgumentException("\"{$board->getFullSku()}\" does not have a PCB in its BOM.");
    }

    private function populateFromXy(ItemVersion $board,
                                    string $csvDataString,
                                    bool $includeDnp = false): string
    {
        $csv = new CsvFileWithHeadings();
        $csv->parseString($csvDataString);

        if (!$csv->hasHeading('gumstix_sku')) {
            $this->insertSku($csv, $board);
        }
        if (!$csv->hasHeading('schematic_value')) {
            $this->insertSchematicValue($csv, $board);
        }
        if (!$csv->hasHeading('mpn')) {
            $this->insertMpn($csv, $board);
        }
        if (!$csv->hasHeading('alternate_mpn')) {
            $this->insertAlternateMpn($csv, $board);
        }

        $this->remapSide($csv);

        $csv = $csv->remap([
            'name' => 'ref_des',
            'gumstix_sku' => 'gumstix_sku',
            'x (mm)' => 'x (mm)',
            'y (mm)' => 'y (mm)',
            'side' => 'side',
            'rotation' => 'rotation',
            'package' => 'package',
            'schematic_value' => 'schematic_value',
            'mpn' => 'mpn',
            'alternate_mpn' => 'alternate_mpn',
        ]);

        if (!$includeDnp) {
            $this->removeDnp($csv);
        }

        return $csv->toString();
    }

    private function insertSku(CsvFileWithHeadings $csv, ItemVersion $board): void
    {
        $bomItems = $board->getBomItems();

        /**
         * [Designator][Package] => SKU
         * @var string[][] $skuMap
         */
        $skuMap = [];
        foreach ($bomItems as $bomItem) {
            foreach ($bomItem->getDesignators() as $designator) {
                $package = $bomItem->getPackage();
                $sku = $bomItem->getSku();

                $skuMap[$designator][$package] = $sku;
            }
        }

        $array = $csv->toArray();
        foreach ($array as &$row) {
            $designator = $row['name'];
            $package = $row['package'];
            $row['gumstix_sku'] = $skuMap[$designator][$package] ?? '';
        }

        $csv->parseArray($array);
    }

    private function insertSchematicValue(CsvFileWithHeadings $csv, ItemVersion $board): void
    {
        $bomItems = $board->getBomItems();

        /**
         * [Designator][Package] => Value
         * @var string[][] $valueMap
         */
        $valueMap = [];
        foreach ($bomItems as $bomItem) {
            foreach ($bomItem->getDesignators() as $designator) {
                $package = $bomItem->getPackage();
                $value = $bomItem->getPartValue();

                $valueMap[$designator][$package] = $value;
            }
        }

        $array = $csv->toArray();
        foreach ($array as &$row) {
            $designator = $row['name'];
            $package = $row['package'];
            $row['schematic_value'] = $valueMap[$designator][$package] ?? 'DNP';
        }

        $csv->parseArray($array);
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
            $row['mpn'] = $mpnMap[$designator][$package] ?? '';
        }

        $csv->parseArray($array);
    }

    private function insertAlternateMpn(CsvFileWithHeadings $csv,
                                        ItemVersion $board): void
    {
        $bomItems = $board->getBomItems();

        /**
         * [Designator][Package] => AML (Alternative MPNs)
         * @var string[][] $mpnMap
         */
        $mpnMap = [];
        foreach ($bomItems as $bomItem) {
            foreach ($bomItem->getDesignators() as $designator) {
                $purchDataList = $this->getPurchasingDataList($bomItem->getSku(), $bomItem->getManufacturerCode());
                $package = $bomItem->getPackage();

                $mpnMap[$designator][$package] = '{' . join(',', $purchDataList) . '}';
            }
        }

        $array = $csv->toArray();
        foreach ($array as &$row) {
            $designator = $row['name'];
            $package = $row['package'];
            $row['alternate_mpn'] = $mpnMap[$designator][$package] ?? '';
        }

        $csv->parseArray($array);
    }

    /**
     *  @return string[]
     */
    private function getPurchasingDataList(string $sku,
                                           string $preferredMPN): array
    {
        $pdArray = $this->purchDataRepo->findAllPurchasingDataBySku($sku);

        $originalMPNArray = array_filter(array_map(function(PurchasingData $pd) {
            if ($pd->getManufacturerCode() != null){
                return $pd->getManufacturerCode();
            } else {
                // return empty string,
                // which will be taken care of by array_filter
                return "";
            }
        }, $pdArray));

        // return array with preferred MPN trimmed
        return array_map(function ($value) {
            return "\"$value\"";
        }, array_filter($originalMPNArray, function($k) use ($preferredMPN) {
            return $preferredMPN !== $k;
        }));
    }


    private function remapSide(CsvFileWithHeadings $csv): void
    {
        $array = $csv->toArray();
        foreach ($array as &$row) {
            $side = $row['side'];
            // Non-strict comparison to account for possible string cast during file write/read.
            $row['side'] = $side == 1 ? 'BOT' : 'TOP';
        }

        $csv->parseArray($array);
    }

    private function removeDnp(CsvFileWithHeadings $csv): void
    {
        $csv->parseArray(array_values(array_filter($csv->toArray(), function($row) {
            return $row['schematic_value'] !== 'DNP';
        })));
    }
}
