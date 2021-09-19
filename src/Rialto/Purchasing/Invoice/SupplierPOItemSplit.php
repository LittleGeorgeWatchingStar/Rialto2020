<?php

namespace Rialto\Purchasing\Invoice;

use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Purchasing\Invoice\Web\SupplierPOItemSplitSoloType;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A line item from a supplier invoice.
 */
class SupplierPOItemSplit
{
    /**
     * @var SupplierPOItemSplitSoloType[]
     * @Assert\Valid(traverse=true)
     */
    private $attachments = [];

    public function __construct()
    {
        $this->grnItems = new ArrayCollection();
    }

    /**
     * @param SupplierPOItemsSplitSolo[] $attachments
     */
    public function setAttachments(array $attachments): void
    {
        $this->attachments = $attachments;
    }

    public function getAttachments()
    {
        return $this->attachments;
    }


}
