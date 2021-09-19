<?php

namespace Rialto\Panelization\IO;


use Gumstix\Filetype\CsvFileWithHeadings;
use Gumstix\Storage\File;
use Gumstix\Storage\FileStorage;
use Rialto\Manufacturing\Bom\Web\BomCsvFile;
use Rialto\Panelization\ConsolidatedBom;
use Rialto\Panelization\ConsolidatedXY;
use Rialto\Purchasing\Order\PurchaseOrder;

class PanelizationStorage
{
    const FILENAME_BOM = 'panelized_bom.csv';
    const FILENAME_XY = 'panelized_xy.csv';
    const FILENAME_LAYOUT = 'panelized_layout.pdf';

    /** @var FileStorage */
    private $storage;

    public function __construct(FileStorage $storage)
    {
        $this->storage = $storage;
    }

    public function storeConsolidatedBom(PurchaseOrder $po, ConsolidatedBom $bom)
    {
        $csv = BomCsvFile::fromComponents($bom->getItems(), null, true);
        $csv->setDelimiter(",");
        $key = $this->getKey($po, self::FILENAME_BOM);
        $this->storage->put($key, $csv->toString());
    }

    private function getKey(PurchaseOrder $po, $filename)
    {
        $poNo = $po->getId();
        return "panelization/$poNo/$filename";
    }

    public function storeConsolidatedXy(PurchaseOrder $po, ConsolidatedXY $xy)
    {
        $csv = new CsvFileWithHeadings();
        $csv->setDelimiter(",");
        $csv->parseArray($xy->toArray());
        $key = $this->getKey($po, self::FILENAME_XY);
        $this->storage->put($key, $csv->toString());
    }

    public function storeLayoutPdf(PurchaseOrder $po, $pdfData)
    {
        $key = $this->getKey($po, self::FILENAME_LAYOUT);
        $this->storage->put($key, $pdfData);
    }

    /**
     * @param PurchaseOrder $po
     * @return File[]
     */
    public function getFiles(PurchaseOrder $po)
    {
        $filenames = [
            self::FILENAME_BOM,
            self::FILENAME_XY,
            self::FILENAME_LAYOUT,
        ];

        $files = [];
        foreach ($filenames as $filename) {
            $key = $this->getKey($po, $filename);
            $files[$filename] = $this->storage->getFile($key);
        }
        return $files;
    }

    /** @return File */
    public function getFile(PurchaseOrder $po, $filename)
    {
        $key = $this->getKey($po, $filename);
        return $this->storage->getFile($key);
    }

    /** @return string */
    public function getFileContents(PurchaseOrder $po, $filename)
    {
        $key = $this->getKey($po, $filename);
        return $this->storage->get($key);
    }

    public function getMimeType(PurchaseOrder $po, $filename)
    {
        $key = $this->getKey($po, $filename);
        return $this->storage->getMimeType($key);
    }
}
