<?php

namespace Rialto\Purchasing\Receiving\Web;

use DateTime;
use Rialto\Purchasing\Order\PurchaseOrder;
use Rialto\Purchasing\Producer\StockProducer;
use Rialto\Purchasing\Receiving\Auth\CanReceiveInto;
use Rialto\Purchasing\Receiving\GoodsReceivedNotice;
use Rialto\Security\User\User;
use Rialto\Stock\Facility\Facility;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Form model for receiving a PO.
 */
class GoodsReceived
{
    /** @var PurchaseOrder */
    private $po;

    /**
     * @var Facility
     * @Assert\NotNull
     * @CanReceiveInto
     */
    public $receivedInto;

    /**
     * @var ItemReceived[]
     * @Assert\Valid(traverse=true)
     */
    public $items = [];

    public $date;

    /** @var boolean */
    public $allowReceive = false;

    public function __construct(PurchaseOrder $po)
    {
        $this->po = $po;
        $this->receivedInto = $po->getDeliveryLocation();
        $this->date = new DateTime();

        $items = $po->getItems();
        // TODO: https://bugs.php.net/bug.php?id=50688 php7
        @usort($items, function (StockProducer $a, StockProducer $b) {
            return strcasecmp($a->getFullSku(), $b->getFullSku());
        });

        foreach ($items as $poItem) {
            if ($poItem->getQtyRemaining() > 0) {
                $purchData = $poItem->getPurchasingData();
                if ($purchData !== null) {
                    // guard that binSize should be more than 0 to be divided
                    $binSize = max(1, $purchData->getBinSize());
                    $remainingQty = $poItem->getQtyRemaining();
                    $numOfbins = ceil($remainingQty/$binSize);
                    $this->items[] = ItemReceived::create($poItem, $numOfbins);
                } else {
                    $this->items[] = ItemReceived::create($poItem);
                }
            }
        }
    }

    /** @return GoodsReceivedNotice */
    public function create(User $receiver)
    {
        $grn = new GoodsReceivedNotice($this->po, $receiver);
        $grn->setReceivedInto($this->receivedInto);
        $grn->setDate($this->date);
        foreach ($this->items as $item) {
            if ($item->getTotalReceived() > 0) {
                $item->addToGrn($grn);
            }
        }
        return $grn;
    }

    public function getAllowReceive()
    {
        return $this->allowReceive;
    }

    public function setAllowReceive(bool $result)
    {
        $this->allowReceive = $result;
    }

    /**
     * Ensure that at least one unit of something is received.
     *
     * @Assert\Callback
     */
    public function validateSomethingReceived(ExecutionContextInterface $context)
    {
        foreach ($this->items as $item) {
            if ($item->getTotalReceived() > 0) {
                return;
            }
        }
        $context->addViolation("Nothing selected to receive.");
    }

    /**
     * Make sure that the quantity of parent work orders plus quantity of
     * child work orders does not exceed the child's total quantity ordered.
     *
     * This is because receiving the parent automatically receives the child, too.
     *
     * @Assert\Callback
     */
    public function validateWorkOrderQuantities(ExecutionContextInterface $context)
    {
        $qty = 0;
        $child = null;
        foreach ($this->items as $item) {
            if ($item->isParent()) {
                $qty += $item->getTotalReceived();
            } elseif ($item->isChild()) {
                $qty += $item->getTotalReceived();
                $child = $item->poItem;
            }
        }
        if ($child && ($rem = $child->getQtyRemaining()) < $qty) {
            $context->addViolation("Cannot receive more of $child ($qty) than remain ($rem)." .
                " Receiving the packaged item also receives the unpackaged one.");
        }
    }

}
