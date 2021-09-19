<?php

namespace Rialto\Stock\Count;

use DateTime;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Gumstix\Filetype\CsvFileWithHeadings;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Level\HistoricalStockLevel;
use Rialto\Stock\Level\Orm\HistoricalStockLevelRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * An uploaded .csv file containing stock counts for a particular location
 * as of a specific date.
 */
class CsvStockCount
{
    /** Show the total changes per sku, omitting bin-level details. */
    const VIEW_SUMMARY = 'summary';

    /** Show the individual bins that will be modified. */
    const VIEW_MODIFIED = 'modified';

    /** Show all matching bins, regardless of whether they will be modified. */
    const VIEW_ALL = 'all';

    /** These columns are required to be in the CSV. The actual column
     * headings can be specified by the user. */
    const COLUMN_SKU = 'sku';
    const COLUMN_QTY = 'qty';


    /**
     * @var Facility The location where the stock count was taken.
     *
     * @Assert\NotNull
     */
    private $location;

    /**
     * @var DateTime The date the stock count was taken.
     *
     * We can retroactively apply any changes.
     *
     * @Assert\NotNull
     * @Assert\LessThan("tomorrow")
     */
    private $asOf;

    /**
     * @var UploadedFile The uploaded csv file
     *
     * @Assert\NotNull
     * @Assert\File(maxSize="10M", mimeTypes={"text/csv", "text/plain"})
     */
    private $uploadedFile;

    /**
     * @var CsvFileWithHeadings The parsed csv file
     */
    private $csvFile;

    /**
     * Allows the user to specify what the column headings are in
     * the uploaded CSV file.
     */
    private $columnHeadings = [
        self::COLUMN_SKU => 'StockID',
        self::COLUMN_QTY => 'QOH',
    ];

    /**
     * How much detail the user wants to see when reviewing the changes
     * specified in the .csv file.
     */
    public $viewDetail = self::VIEW_MODIFIED;

    /** @var HistoricalStockLevel[] */
    private $levels = [];

    /**
     * @return Facility
     */
    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation(Facility $location)
    {
        $this->location = $location;
    }

    /**
     * @return DateTime
     */
    public function getAsOf()
    {
        return $this->asOf ? clone $this->asOf : null;
    }

    public function setAsOf(DateTime $asOf)
    {
        $this->asOf = clone $asOf;
        $this->asOf->setTime(23, 59, 59); // end of day
    }

    /**
     * @return string[]
     */
    public function getColumnHeadings()
    {
        return $this->columnHeadings;
    }

    /**
     * @param string[] $headings
     */
    public function setColumnHeadings(array $headings)
    {
        $this->columnHeadings = $headings;
    }

    /**
     * @Assert\Callback
     */
    public function validateColumnHeadings(ExecutionContextInterface $context)
    {
        foreach ([self::COLUMN_SKU, self::COLUMN_QTY] as $col) {
            $heading = $this->columnHeadings[$col];
            if (! $this->csvFile->hasHeading($heading)) {
                $context->buildViolation("File is missing required heading '$heading'.")
                    ->atPath('uploadedFile')
                    ->addViolation();
            }
        }
    }

    /**
     * @return UploadedFile
     */
    public function getUploadedFile()
    {
        return $this->uploadedFile;
    }

    public function setUploadedFile(UploadedFile $uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;
        $this->csvFile = new CsvFileWithHeadings();
        $this->csvFile->parseFile($uploadedFile->getRealPath());
    }

    /**
     * @return CsvFileWithHeadings
     */
    public function getCsvFile()
    {
        return $this->csvFile;
    }

    public function setCsvFile(CsvFileWithHeadings $csvFile)
    {
        $this->csvFile = $csvFile;
    }

    public function loadLevels(EntityManagerInterface $em)
    {
        $repo = new HistoricalStockLevelRepository($em);
        $bins = $repo->findBins($this->location, $this->asOf);
        $this->levels = HistoricalStockLevel::fromBins($bins);
    }

    /**
     * @return HistoricalStockLevel[]
     */
    public function getLevels()
    {
        return $this->levels;
    }

    /**
     * @param HistoricalStockLevel[] $levels
     */
    public function applyAdjustments()
    {
        foreach ($this->csvFile as $row) {
            $sku = $this->getSkuFromRow($row);
            if (! $sku) {
                continue;
            }
            if (empty($this->levels[$sku])) {
                continue;
            }
            $level = $this->levels[$sku];
            $qty = $this->getQtyFromRow($row);
            $level->setReportedQty($qty);
        }
    }

    private function getSkuFromRow(array $row)
    {
        $heading = $this->columnHeadings[self::COLUMN_SKU];
        return $row[$heading];
    }

    private function getQtyFromRow(array $row)
    {
        $heading = $this->columnHeadings[self::COLUMN_QTY];
        return $row[$heading];
    }

    public function isVisible(HistoricalStockLevel $level)
    {
        switch ($this->viewDetail) {
            case self::VIEW_ALL:
                return true;
            default:
                return $level->getRequiredAdjustment() != 0;
        }
    }

    public function isShowBins()
    {
        return self::VIEW_SUMMARY != $this->viewDetail;
    }

    /**
     * The total change in stock value, if all adjustments are applied.
     *
     * @return float|int
     */
    public function getTotalStandardCostDiff()
    {
        $total = 0;
        foreach ($this->levels as $level) {
            $total += $level->getStandardCostDiff();
        }
        return $total;
    }

    /**
     * Positive adjustments result in new bins being created, which need
     * to be persisted before the stock adjustment can be made.
     */
    public function persistNewBins(ObjectManager $om)
    {
        foreach ($this->levels as $level) {
            foreach ($level->getBins() as $hbin) {
                $hbin->persistNewBin($om);
            }
        }
    }

    /**
     * The final step is to create the actual stock adjustment.
     *
     * @return StockAdjustment
     */
    public function createStockAdjustment()
    {
        $adjustment = new StockAdjustment(sprintf('Stock count CSV upload from %s',
            $this->location));
        $adjustment->setDate($this->asOf);
        foreach ($this->levels as $level) {
            foreach ($level->getBins() as $hbin) {
                $hbin->addToStockAdjustment($adjustment);
            }
        }
        return $adjustment;
    }
}
