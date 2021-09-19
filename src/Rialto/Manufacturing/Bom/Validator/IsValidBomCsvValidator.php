<?php

namespace Rialto\Manufacturing\Bom\Validator;

use Rialto\Database\Orm\DbManager;
use Rialto\Manufacturing\Bom\Web\BomCsvFile;
use Rialto\Manufacturing\WorkType\WorkType;
use Rialto\Stock\Item\Orm\StockItemRepository;
use Rialto\Stock\Item\StockItem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validates whether an uploaded file can be parsed into a BomCsvFile
 */
class IsValidBomCsvValidator extends ConstraintValidator
{
    /** @var DbManager */
    private $dbm;

    public function __construct(DbManager $dbm)
    {
        $this->dbm = $dbm;
    }

    /**
     * @param File $file
     */
    public function validate($uploadedFile, Constraint $constraint)
    {
        $file = BomCsvFile::fromUploadedFile($uploadedFile);

        if ($this->validateHeadings($file->getHeadings())) {
            $this->validateLines($file->getLines());
        }
    }

    /**
     * @return bool Don't bother validating lines if the headings are invalid.
     */
    private function validateHeadings(array $headings)
    {
        $valid = true;
        foreach (BomCsvFile::getRequiredHeadings() as $required) {
            if (! in_array($required, $headings)) {
                $this->context->addViolation("Missing required heading '$required'.");
                $valid = false;
            }
        }
        return $valid;
    }

    private function validateLines(array $lines)
    {
        if (count($lines) < 1) {
            $this->context->addViolation('File must include at least one item.');
            return;
        }
        foreach ($lines as $i => $line) {
            $lineNo = $i + 2; // zero-based indexing plus heading line
            $this->validateLine($lineNo, $line);
        }
    }

    private function validateLine($lineNo, $line)
    {
        /** @var $itemRepo StockItemRepository */
        $itemRepo = $this->dbm->getRepository(StockItem::class);
        $workTypeRepo = $this->dbm->getRepository(WorkType::class);

        foreach ($line as $column => $value) {
            if ($this->isRequired($column) && empty($value)) {
                $this->context->addViolation("Line $lineNo: missing required value '$column'.");
                continue;
            }

            switch ($column) {
                case BomCsvFile::HEADING_SKU:
                    if (! $itemRepo->isExistingStockId($value)) {
                        $this->context->addViolation("Line $lineNo: no item with SKU $value exists.");
                    }
                    break;
                case BomCsvFile::HEADING_QTY:
                    if (! is_numeric($value)) {
                        $this->context->addViolation("Line $lineNo: quantity must be numeric.");
                    } elseif ($value <= 0) {
                        $this->context->addViolation("Line $lineNo: quantity must be greater than zero.");
                    }
                    break;
                case BomCsvFile::HEADING_WORKTYPE:
                    if (! $workTypeRepo->find($value)) {
                        $this->context->addViolation("Line $lineNo: no such work type $value.");
                    }
                    break;
            }
        }
    }

    private function isRequired($column)
    {
        return in_array($column, BomCsvFile::getRequiredHeadings());
    }
}
