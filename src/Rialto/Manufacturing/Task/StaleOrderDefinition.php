<?php

namespace Rialto\Manufacturing\Task;

use Rialto\Manufacturing\Task\Cli\ProductionTaskReminderCommand;
use Rialto\Purchasing\Supplier\Contact\SupplierContact;
use Rialto\Stock\Facility\Facility;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Defines what counts as a stale work order PO.
 *
 * When a PO becomes stale, we can pester the supplier to update it.
 *
 * @see ProductionTaskReminderCommand
 */
class StaleOrderDefinition
{
    /**
     * @var Facility
     */
    public $location;

    /**
     * @var int
     * Assert\Type(type="number", )
     * @Assert\Range(min=1,
     *     minMessage="stale_order_def.age_min",
     *     invalidMessage="stale_order_def.age_invalid")
     */
    public $age = 72; // hours

    public $rework = false;

    public $inProgress = false;

    /** @var \DateTime */
    public $asOf;

    public function __construct(Facility $location)
    {
        $this->location = $location;
        $this->asOf = new \DateTime();
    }

    /**
     * @return SupplierContact[]
     *
     * @Assert\Count(min=1, minMessage="stale_order_def.contacts")
     */
    public function getRecipients()
    {
        return $this->location->hasSupplier()
            ? $this->location->getSupplier()->getKitContacts()
            : [];
    }
}
