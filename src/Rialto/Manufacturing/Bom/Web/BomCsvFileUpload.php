<?php

namespace Rialto\Manufacturing\Bom\Web;

use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\Bom\Validator\IsValidBomCsv;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\ItemVersion;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class BomCsvFileUpload
{
    /**
     * @var ItemVersion
     */
    private $parent;

    /**
     * @Assert\File(
     *      maxSize = "1M",
     *      mimeTypes = {"text/csv", "text/plain"},
     *      mimeTypesMessage = "Please upload a valid csv file"
     * )
     * @IsValidBomCsv
     * @var UploadedFile
     */
    private $attachment;

    public function __construct(ItemVersion $parent)
    {
        $this->parent = $parent;
    }

    public function setAttachment(UploadedFile $file)
    {
        $this->attachment = $file;
    }

    public function getAttachment()
    {
        return $this->attachment;
    }

    public function updateBomFromCsv(DbManager $dbm)
    {
        $this->parent->clearBom();
        $dbm->flush();  // prevent duplicate key errors
        $csv = BomCsvFile::fromUploadedFile($this->attachment);
        $this->loadFromCsv($csv, $dbm);
    }

    /**
     * Loads components of an item version from a csv file
     */
    private function loadFromCsv(BomCsvFile $csv, DbManager $dbm)
    {
        $partsHeading = BomCsvFile::HEADING_DESIGNATORS;
        foreach ($csv as $row) {
            $component = $dbm->need(StockItem::class, $csv->getStockCode($row));
            $quantity = $csv->getQuantity($row);
            $componentVersion = $csv->getComponentVersion($row, $component);
            $bomItem = $this->parent->addComponent($component, $quantity);
            $bomItem->setVersion($componentVersion);
            $bomItem->setParent($this->parent);
            if (isset($row[$partsHeading])) {
                $bomItem->setDesignators($csv->getDesignators($row));
            }
            $workType = $dbm->need(WorkType::class, $row[BomCsvFile::HEADING_WORKTYPE]);
            $bomItem->setWorkType($workType);
        }
    }
}

