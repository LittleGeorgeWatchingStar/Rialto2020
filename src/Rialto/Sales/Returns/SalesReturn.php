<?php

namespace Rialto\Sales\Returns;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Accounting\Debtor\DebtorInvoice;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Database\Orm\DbManager;
use Rialto\Entity\RialtoEntity;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Customer\CustomerBranch;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Security\User\User;
use Rialto\Stock\Move\StockMove;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;


class SalesReturn implements RialtoEntity
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var DateTime
     */
    private $dateAuthorized;

    /**
     * @var integer
     * @Assert\Type(type="integer", message="not an int")
     */
    private $caseNumber = 0;

    /** @var DebtorInvoice */
    private $originalInvoice;

    /**
     * @var SalesReturnItem[]
     * @Assert\Valid(traverse=true)
     */
    private $lineItems;
    private $authorizedBy;

    /** @var SalesOrder|null */
    private $replacementOrder;

    /**
     * Customer Branch of the engineer who is to receive items in the
     * case of the engineering disposition.
     * @var CustomerBranch
     */
    private $engineerBranch;

    /**
     * The tracking number(s) of the customer's return shipment(s) to us.
     *
     * @var string
     * @Assert\Length(max=255)
     */
    private $trackingNumber = '';

    public $createReplacementOrder = false;
    public $shipImmediately = false;


    public function __construct(DebtorInvoice $invoice, User $authorizedBy)
    {
        $this->originalInvoice = $invoice;
        $this->authorizedBy = $authorizedBy;
        $this->dateAuthorized = new DateTime();
        $this->lineItems = new ArrayCollection();
        $this->populateMissingItemsForEditing();
    }

    public function populateMissingItemsForEditing()
    {
        foreach ($this->originalInvoice->getStockMoves() as $move) {
            $this->ensureItemExists($move);
        }
    }

    private function ensureItemExists(StockMove $move)
    {
        foreach ($this->lineItems as $item) {
            if ($item->isForStockMove($move)) {
                return;
            }
        }
        $this->lineItems[] = new SalesReturnItem($this, $move);
    }

    public function pruneUnauthorizedItems()
    {
        foreach ($this->lineItems as $item) {
            if ($item->getQtyAuthorized() <= 0) {
                $this->lineItems->removeElement($item);
            }
        }
    }

    /** @Assert\Callback */
    public function validateAtLeastOneItemAuthorized(ExecutionContextInterface $context)
    {
        foreach ($this->lineItems as $item) {
            if ($item->getQtyAuthorized() > 0) {
                return;
            }
        }
        $context->addViolation("You must authorize at least one item for return.");
    }

    public function loadOriginalProducers(DbManager $dbm)
    {
        foreach ($this->lineItems as $item) {
            $item->loadOriginalProducer($dbm);
        }
    }

    public function isNew()
    {
        return !$this->id;
    }

    /**
     * Get line items
     *
     * @return SalesReturnItem[]
     */
    public function getLineItems()
    {
        return $this->lineItems->toArray();
    }

    public function getEngineerBranch()
    {
        return $this->engineerBranch;
    }

    public function setEngineerBranch(CustomerBranch $branch = null)
    {
        $this->engineerBranch = $branch;
    }

    /** @return int */
    public function getTotalQtyAuthorized()
    {
        $total = 0;
        foreach ($this->lineItems as $item) {
            $total += $item->getQtyAuthorized();
        }
        return $total;
    }

    /** @return int */
    public function getTotalQtyReceived()
    {
        $total = 0;
        foreach ($this->lineItems as $item) {
            $total += $item->getQtyReceived();
        }
        return $total;
    }

    /** @return int */
    public function getTotalQtyTested()
    {
        $total = 0;
        foreach ($this->lineItems as $item) {
            $total += $item->getQtyTested();
        }
        return $total;
    }

    public function getOriginalInvoice(): DebtorInvoice
    {
        return $this->originalInvoice;
    }

    public function getOriginalOrder(): SalesOrder
    {
        $invoice = $this->getOriginalInvoice();
        return $invoice->getSalesOrder();
    }

    public function getOriginalOrderNumber()
    {
        return $this->getOriginalOrder()->getOrderNumber();
    }

    public function getCustomer(): Customer
    {
        $invoice = $this->getOriginalInvoice();
        return $invoice->getCustomer();
    }

    /** @return GLAccount */
    public function getTaxAccount()
    {
        $order = $this->getOriginalOrder();
        $branch = $order->getCustomerBranch();
        return $branch->getTaxAccount();
    }

    /** @return string */
    public function getCustomerName()
    {
        return $this->getCustomer()->getName();
    }

    /**
     * @return DateTime
     */
    public function getDateAuthorized()
    {
        return $this->dateAuthorized;
    }

    /**
     * @param integer $caseNumber
     */
    public function setCaseNumber($caseNumber)
    {
        $this->caseNumber = (int) $caseNumber;
    }

    /**
     * @return integer
     */
    public function getCaseNumber()
    {
        return $this->caseNumber;
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /** @return string */
    public function getRmaNumber()
    {
        return sprintf('RMA %s', $this->id);
    }

    public function __toString()
    {
        return $this->getRmaNumber();
    }

    /**
     * @return User
     */
    public function getAuthorizedBy()
    {
        return $this->authorizedBy;
    }

    public function setReplacementOrder(SalesOrder $order)
    {
        $this->replacementOrder = $order;
    }

    public function hasReplacementOrder()
    {
        return (bool) $this->replacementOrder;
    }

    /** @return SalesOrder|null */
    public function getReplacementOrder()
    {
        return $this->replacementOrder;
    }

    /**
     * @return string
     */
    public function getTrackingNumber()
    {
        return $this->trackingNumber;
    }

    /**
     * @param string $tracking
     */
    public function setTrackingNumber($tracking)
    {
        $this->trackingNumber = trim($tracking);
    }

    public function syncReplacementOrder(DbManager $dbm)
    {
        if (!$this->replacementOrder) {
            if ($this->createReplacementOrder) {
                $this->replacementOrder = SalesOrder::fromSalesReturn($this);
                $dbm->persist($this->replacementOrder);
                if ($this->shipImmediately) {
                    $this->replacementOrder->convertToOrder();
                }
            } else {
                return;
            }
        }

        $groups = $this->getItemGroups();
        foreach ($groups as $itemGroup) {
            $itemGroup->updateReplacementOrder();
        }
        foreach ($this->replacementOrder->getLineItems() as $orderItem) {
            $stockCode = $orderItem->getSku();
            if (empty($groups[$stockCode]) && (!$orderItem->isInvoiced())) {
                $this->replacementOrder->removeLineItem($orderItem);
            }
        }
    }

    /**
     * @return SalesReturnItemGroup[] The items of this sales return, grouped
     *   by stock item.
     */
    private function getItemGroups()
    {
        $groups = [];
        foreach ($this->lineItems as $rmaItem) {
            $key = $rmaItem->getSku();
            if (empty($groups[$key])) {
                $groups[$key] = new SalesReturnItemGroup();
            }
            $groups[$key]->addItem($rmaItem);
        }
        return $groups;
    }
}
