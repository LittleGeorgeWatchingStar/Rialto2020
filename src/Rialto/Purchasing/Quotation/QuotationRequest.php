<?php

namespace Rialto\Purchasing\Quotation;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Entity\RialtoEntity;
use Rialto\Purchasing\Supplier\Supplier;
use Rialto\Security\User\User;
use Rialto\Stock\Item\PhysicalStockItem;

/**
 * A request for suppliers to let us know how much they will
 * charge for a part. The quotes that they provide will be used to create
 * PurchasingData records.
 *
 * Also known as a Request for Quotation (RFQ).
 */
class QuotationRequest implements RialtoEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var User
     */
    private $requestedBy;

    /**
     * @var Supplier
     */
    private $supplier;

    /**
     * @var string
     */
    private $comments;

    /**
     * @var DateTime
     */
    private $dateSent;

    /**
     * @var DateTime
     */
    private $dateReceived;

    /**
     * @var QuotationRequestItem[]
     */
    private $items;

    /**
     * @var bool
     */
    private $isTurboGeppetto = false;


    public function __construct(User $requestedBy, Supplier $supplier)
    {
        $this->requestedBy = $requestedBy;
        $this->supplier = $supplier;
        $this->items = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    public function __toString()
    {
        return sprintf('RFQ %s for %s', $this->id, $this->supplier);
    }

    /**
     * @return User
     */
    public function getRequestedBy()
    {
        return $this->requestedBy;
    }

    /**
     * @return Supplier
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @param string $comments
     */
    public function setComments($comments)
    {
        $this->comments = trim($comments);
    }

    /**
     * @return string
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @return DateTime
     */
    public function getDateSent()
    {
        return $this->dateSent ? clone $this->dateSent : null;
    }

    public function isSent()
    {
        return null !== $this->dateSent;
    }

    public function setSent()
    {
        $this->dateSent = new DateTime();
    }

    /**
     * @return DateTime
     */
    public function getDateReceived()
    {
        return $this->dateReceived ? clone $this->dateReceived : null;
    }

    /** @return QuotationRequestItem */
    public function createItem(PhysicalStockItem $item)
    {
        $quoteItem = new QuotationRequestItem($this, $item);
        $this->items[] = $quoteItem;
        return $quoteItem;
    }

    /**
     * @return QuotationRequestItem[]
     */
    public function getItems()
    {
        return $this->items->getValues();
    }

    public function isTurboGeppetto(): bool
    {
        return $this->isTurboGeppetto;
    }

    public function setTurboGeppetto(bool $value)
    {
        $this->isTurboGeppetto = $value;
    }
}

