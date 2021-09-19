<?php

namespace Rialto\Sales\Returns\Disposition;

use Rialto\Sales\Returns\SalesReturn;
use Rialto\Sales\Returns\SalesReturnItem;
use Rialto\Stock\Facility\Facility;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


/**
 * Records the test results of a sales return.
 */
class SalesReturnResults
extends SalesReturnProcessing
{
    /**
     * Where stock is kept while awaiting testing.
     * @var Facility
     */
    private $testingLoc;

    /**
     * Where stock that has passed testing is sent.
     * @var Facility
     */
    private $workingLoc;

    /**
     * The items being tested.
     *
     * @var SalesReturnItemResults[]
     * @Assert\Valid(traverse="true")
     */
    private $items = [];

    public function __construct(
        SalesReturn $rma,
        Facility $testingLoc,
        Facility $workingLoc)
    {
        parent::__construct($rma);
        $this->testingLoc = $testingLoc;
        $this->workingLoc = $workingLoc;
    }

    public function createItem(SalesReturnItem $rmaItem)
    {
        $item = new SalesReturnItemResults(
            $rmaItem,
            $this->testingLoc,
            $this->workingLoc
        );
        return $item;
    }

    public function addItem(SalesReturnItemResults $dispItem)
    {
        $this->items[] = $dispItem;
    }

    public function getItems()
    {
        return $this->items;
    }

    /**
     * @Assert\Callback
     */
    public function assertSomethingSelected(ExecutionContextInterface $context)
    {
        foreach ( $this->items as $item ) {
            if ( $item->getQtyPassed() != 0 ) return;
            if ( $item->getQtyFailed() != 0 ) return;
        }

        $context->buildViolation("Nothing entered.")->atPath('items')->addViolation();
    }

}
