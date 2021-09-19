<?php

namespace Rialto\Purchasing\Quotation;

use Gumstix\Filetype\CsvFile;
use Rialto\Alert\HasWarnings;
use Rialto\Database\Orm\DbManager;
use Rialto\Purchasing\Catalog\CostBreak;
use Rialto\Purchasing\Catalog\Orm\CostBreakRepository;
use Rialto\Purchasing\Catalog\Orm\PurchasingDataRepository;
use Rialto\Purchasing\Catalog\PurchasingData;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * Maps fields from a supplier quotation .csv file to the fields of
 * purchasing data records.
 */
class QuotationCsvMapping
{
    use HasWarnings;

    /** @var Supplier */
    private $supplier;

    private $quoteNo;

    private $mapping = [];

    private $csvData = [];

    /** @var PurchasingDataRepository */
    private $dataRepo = null;

    /** @var CostBreakRepository */
    private $costRepo = null;

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function getQuotationNumber()
    {
        return $this->quoteNo;
    }

    public function setQuotationNumber($quoteNo)
    {
        $this->quoteNo = $quoteNo;
    }

    public function getMapping()
    {
        return $this->mapping;
    }

    public function setMapping(array $mapping)
    {
        $this->mapping = $mapping;
    }

    public function getCsvData()
    {
        return $this->csvData;
    }

    public function setCsvData(array $csvData)
    {
        $this->csvData = $csvData;
    }

    public function setCsvFile(CsvFile $file)
    {
        $this->mapping = array_fill(0, $file->getNumColumns(), '');
        $this->csvData = $file->toArray();
    }

    private function remapData()
    {
        $csvFile = new CsvFile();
        $csvFile->parseArray($this->csvData);
        return $csvFile->remap($this->mapping);
    }

    public function createPurchasingData(DbManager $dbm)
    {
        $csvFile = $this->remapData();
        $this->dataRepo = $dbm->getRepository(PurchasingData::class);
        $this->costRepo = $dbm->getRepository(CostBreak::class);
        $records = [];
        foreach ( $csvFile as $line ) {
            $stockItem = $dbm->find(StockItem::class, $line['stockCode']);
            if (! $stockItem ) {
                $this->addWarning(sprintf(
                    "Skipping invalid stock item \"%s\".",
                    $line['stockCode']
                ));
                continue;
            }
            $catalogNo = $line['catalogNumber'];
            if ( empty($records[$catalogNo])) {
                $records[$catalogNo] = $this->findOrCreatePurchData($stockItem, $line);
            }

            $purchData = $records[$catalogNo];
            $costBreak = $this->createCostBreak($line);
            $purchData->addCostBreak($costBreak);
        }
        return array_values($records);
    }

    private function findOrCreatePurchData(StockItem $stockItem, array $line)
    {
        $catalogNo = $line['catalogNumber'];

        $purchData = $stockItem->isManufactured() ?
            $this->findPurchDataByLocation($stockItem, $catalogNo) :
            $this->findPurchDataBySupplier($stockItem, $catalogNo);

        $this->setField($purchData, 'manufacturerCode', $line);
        $this->setField($purchData, 'supplierDescription', $line);
        $this->setField($purchData, 'incrementQty', $line);
        $this->setField($purchData, 'binSize', $line);
//                $purchData->setRoHS(new RoHSStatus($line['RoHS']));
        $this->costRepo->deleteByPurchasingData($purchData);
        return $purchData;
    }

    private function findPurchDataByLocation(StockItem $stockItem, $catalogNo)
    {
        $location = $this->supplier->getFacility();
        $purchData = $this->dataRepo->findUniqueByLocation(
            $location, $catalogNo, $this->quoteNo
        );
        if ( ! $purchData ) {
            $purchData = $this->createPurchData($stockItem, $catalogNo);
            $purchData->setBuildLocation($location);
        }
        return $purchData;
    }

    private function findPurchDataBySupplier(StockItem $stockItem, $catalogNo)
    {
        $purchData = $this->dataRepo->findUnique(
            $this->supplier, $catalogNo, $this->quoteNo
        );
        if ( ! $purchData ) {
            $purchData = $this->createPurchData($stockItem, $catalogNo);
            $purchData->setSupplier($this->supplier);
        }
        return $purchData;
    }

    private function createPurchData(StockItem $stockItem, $catalogNo)
    {
        $purchData = new PurchasingData($stockItem);
        $purchData->setCatalogNumber($catalogNo);
        $purchData->setQuotationNumber($this->quoteNo);
        return $purchData;
    }

    private function setField($object, $field, array $data)
    {
        if ( empty($data[$field])) return;
        $method = 'set' . ucfirst($field);
        $object->$method( $data[$field] );
    }

    private function createCostBreak(array $line)
    {
        $costBreak = new CostBreak();
        $this->setField($costBreak, 'cost', $line);
        $this->setField($costBreak, 'leadTime', $line);
        $this->setField($costBreak, 'minimumOrderQty', $line);
        return $costBreak;
    }

    private function getRequiredFields()
    {
        return [
            'catalogNumber',
            'stockCode',
            'minimumOrderQty',
            'leadTime',
            'incrementQty',
            'cost',
        ];
    }

    /**
     * @Assert\Callback()
     */
    public function validateMapping(ExecutionContextInterface $context)
    {
        $missing = [];
        foreach ( $this->getRequiredFields() as $field ) {
            if (! in_array($field, $this->mapping) ) $missing[] = $field;
        }
        if (! empty($missing) ) {
            $context->addViolation("Missing required field(s) _fields.", [
                '_fields' => join(', ', $missing),
            ]);
            return;
        }

        $csv = $this->remapData();
        foreach ( $csv as $number => $line ) {
            foreach ( $this->getRequiredFields() as $field ) {
                if ( empty($line[$field])) {
                    $context->addViolation(
                        "Line _num is missing required field _field.", [
                            '_num' => $number + 1,
                            '_field' => $field,
                    ]);
                }
            }
        }
    }
}
