<?php

namespace Rialto\Purchasing\Order;

use DateTime;
use Rialto\Email\Mailable\Mailable;
use Rialto\Entity\RialtoEntity;

/**
 * Records when a purchase order was sent and by whom.
 */
class OrderSent implements RialtoEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var PurchaseOrder
     */
    private $purchaseOrder;

    /**
     * @var DateTime
     */
    private $dateSent;

    /**
     * @var string
     */
    private $sender;

    /**
     * @var string
     */
    private $note;

    /**
     * @var string|null
     */
    private $fileName;

    public function __construct(PurchaseOrder $order, $sender, $note, ?string $fileName = null)
    {
        if ($fileName) {
            $this->fileName = $fileName;
        }
        $this->purchaseOrder = $order;
        $this->dateSent = new DateTime();
        $this->sender = ($sender instanceof Mailable)
            ? $sender->getName()
            : trim($sender);
        $this->note = trim($note);
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function getDateSent()
    {
        return clone $this->dateSent;
    }

    /**
     * @return string
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }
}

