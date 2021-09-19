<?php

namespace Rialto\Allocation\Allocation;

use Rialto\Allocation\Requirement\Requirement;
use Rialto\Allocation\Source\BasicStockSource;
use Rialto\Allocation\Source\CompatibilityChecker;
use Rialto\Database\Orm\DbKeyException;
use Zend\Validator\ValidatorInterface;

/**
 * Checks to make sure a StockAllocation is valid.
 *
 * @deprecated Use Symfony validation instead
 */
class AllocationValidator implements ValidatorInterface
{
    const WRONG_CLASS = 'wrong class';
    const INVALID_ITEM = 'invalid stock item';
    const INVALID_QTY = 'invalid quantity';
    const INVALID_REQUIREMENT = 'invalid requirement';
    const INVALID_SOURCE = 'invalid source';
    const INVALID_TYPE = 'invalid type';
    const OVER_ALLOCATED = 'over-allocated';
    const OVER_ALLOCATED_SOURCE = 'stock source is over-allocated';
    const VERSION_MISMATCH = 'version or customization mismatch';

    private $errors = [];

    public function isValid($alloc)
    {
        $this->errors = [];
        if (! $alloc instanceof StockAllocation ) {
            $this->errors[] = self::WRONG_CLASS;
            return false; /* fatal */
        }

        try {
            $requirement = $alloc->getRequirement();
        }
        catch ( InvalidAllocationException $ex ) {
            $this->errors[] = self::INVALID_REQUIREMENT;
            return false;
        }

        try {
            $source = $alloc->getSource();
        }
        catch ( DbKeyException $ex ) {
            $this->errors[] = self::INVALID_SOURCE;
            return false;
        }
        catch ( InvalidAllocationException $ex ) {
            $this->errors[] = self::INVALID_SOURCE;
            return false;
        }
        if (! $source instanceof BasicStockSource ) {
            $this->errors[] = self::INVALID_SOURCE;
            return false;
        }

        if ( $alloc->isVersioned() ) {
            $this->checkVersions($source, $requirement);
        }

        if ( $alloc->getQtyAllocated() < 0 ) {
            $this->errors[] = self::INVALID_QTY;
        }
        if ( $source->getQtyRemaining() < $alloc->getQtyAllocated() ) {
            $this->errors[] = self::OVER_ALLOCATED;
        }
        // This breaks work order issuing.
//        if ($requirement->getTotalQtyUndelivered() < $alloc->getNetQtyAllocated()) {
//            $this->errors[] = self::OVER_ALLOCATED;
//        }
        if ( $source->getQtyUnallocated() < 0 ) {
            $this->errors[] = self::OVER_ALLOCATED_SOURCE;
        }

        return count($this->errors) == 0;
    }

    private function checkVersions(BasicStockSource $source, Requirement $requirement)
    {
        $checker = new CompatibilityChecker();

        if (! $checker->areCompatible($source, $requirement) ) {
            $this->errors[] = self::VERSION_MISMATCH;
        }
    }

    public function getMessages()
    {
        return $this->errors;
    }
}
