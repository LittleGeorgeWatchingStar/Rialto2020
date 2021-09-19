<?php

namespace Rialto\Sales\Returns\Receipt;

use Rialto\Accounting\Money;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Allocation\Requirement\RequirementCollection;
use Rialto\Allocation\Source\CompatibilityChecker;
use Rialto\Allocation\Source\StockSource;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Sales\Order\TaxableOrderItem;
use Rialto\Sales\Returns\Disposition\SalesReturnItemProcessing;
use Rialto\Sales\Returns\SalesReturn;
use Rialto\Sales\Returns\SalesReturnItem;
use Rialto\Stock\Bin\BinStyle;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Records the receipt of an item that was returned to us as part of an RMA.
 *
 * @see SalesReturnReceipt
 * @see SalesReturn
 */
class SalesReturnItemReceipt
    extends SalesReturnItemProcessing
    implements StockSource, TaxableOrderItem
{
    /** @var SalesReturnReceipt */
    private $receipt;

    /**
     * @var integer
     * @Assert\NotBlank(message = "Quantity cannot be blank")
     * @Assert\Type(type = "int", message = "Quantity must be an integer")
     * @Assert\Range(min = "0", minMessage = "Quantity cannot be negative")
     */
    private $quantity = 0;

    /**
     * @var BinStyle
     * @Assert\NotNull
     */
    private $binStyle;

    public function __construct(
        SalesReturnReceipt $receipt,
        SalesReturnItem $rmaItem)
    {
        parent::__construct($rmaItem);
        $this->receipt = $receipt;
    }

    public function getQuantity()
    {
        return $this->quantity;
    }

    public function setQuantity($qty)
    {
        $this->quantity = (int) $qty;
    }

    public function getBinStyle()
    {
        return $this->binStyle;
    }

    public function setBinStyle(BinStyle $binStyle)
    {
        $this->binStyle = $binStyle;
    }

    public function getQtyAuthorized()
    {
        return $this->rmaItem->getQtyAuthorized();
    }

    public function getQtyPreviouslyReceived()
    {
        return $this->rmaItem->getQtyReceived();
    }

    public function getOriginalWorkOrder()
    {
        return $this->rmaItem->getOriginalWorkOrder();
    }

    /**
     * @Assert\Callback
     */
    public function assertQuantityValid(ExecutionContextInterface $context)
    {
        $qtyAuth = $this->rmaItem->getQtyAuthorized();
        $qtyRecd = $this->rmaItem->getQtyReceived();
        $totalRecd = $qtyRecd + $this->quantity;
        if ($totalRecd > $qtyAuth) {
            $context->buildViolation(
                "You cannot receive more than has been authorized.")
                ->atPath('quantity')
                ->addViolation();
        }
    }

    /**
     * @param Transaction $transaction
     * @return StockBin
     */
    public function receive(Transaction $transaction)
    {
        $this->rmaItem->addQtyReceived($this->quantity);
        $bin = $this->createNewStock($transaction);
        $bin->setAllocatable(false);

        return $bin;
    }

    /** @return StockBin */
    private function createNewStock(Transaction $transaction)
    {
        $intoLoc = $this->receipt->getReceivingLocation();

        $originalBin = $this->rmaItem->getOriginalStockBin();

        $newBin = $originalBin ? (clone $originalBin) :
            $this->createNewBin($intoLoc);
        /* @var $newBin StockBin */

        // Prefer the original work order version to the original bin version.
        $newBin->setVersion($this->rmaItem->getVersion());
        $newBin->setNewQty($this->quantity);
        $newBin->setBinStyle($this->binStyle);
        $newBin->setLocation($intoLoc);

        $stockMove = $newBin->applyNewQty($transaction);
        $stockMove->setForSalesOrderItem($this->rmaItem);
        return $newBin;
    }

    /**
     * Creates a new stock bin from scratch for the returned parts
     * because the original cannot be found.
     *
     * @return StockBin
     */
    private function createNewBin(Facility $loc)
    {
        $originalMove = $this->rmaItem->getOriginalStockMove();
        $bin = new StockBin(
            $this->rmaItem->getStockItem(),
            $loc,
            $this->rmaItem->getVersion());
        $bin->setCustomization($this->rmaItem->getCustomization());
        $bin->setPurchaseCost($originalMove->getUnitStandardCost());

        return $bin;
    }

    public function addStockEntries(Transaction $glTrans)
    {
        $stdCost = $this->rmaItem->getStandardCost();
        if (0 == $stdCost) return;

        $customer = $this->rmaItem->getCustomer();
        $value = round($stdCost * $this->quantity, 2);
        $memo = sprintf('%s - %s x %s @ %s',
            $customer->getId(),
            $this->getSku(),
            number_format($this->quantity),
            number_format($stdCost, 4)
        );

        /* Cost of goods sold goes down, because we're getting those
         * goods back again. */
        $cogsAcct = $this->rmaItem->getCogsAccount();
        $glTrans->addEntry($cogsAcct, -$value, $memo);

        /* Stock account goes back up, since the items are coming back
         * into stock. */
        $stockAcct = $this->rmaItem->getStockAccount();
        $glTrans->addEntry($stockAcct, $value, $memo);
    }

    public function addDebtorEntries(Transaction $glTrans)
    {
        $price = $this->rmaItem->getFinalUnitPrice();
        if (0 == $price) {
            return;
        }

        $customer = $this->rmaItem->getCustomer();

        /* The sales account originally decreased to offset prepaid revenue.
         * So now, when getting the product back, sales goes back up again. */
        $salesAcct = $this->rmaItem->getSalesAccount();
        $undiscountedExtendedPrice = round($price * $this->quantity, 2);
        $memo = sprintf('%s - %s x %s @ %s',
            $customer->getId(),
            $this->getSku(),
            number_format($this->quantity),
            number_format($price, 2)
        );

        $glTrans->addEntry($salesAcct, $undiscountedExtendedPrice, $memo);

        /* Handle the discount amount */
        $discountRate = $this->rmaItem->getDiscountRate();
        if (0 == $discountRate) {
            return;
        }

        /* The discount account was originally credited with the amount
         * of the discount, so now we must debit it. */
        $discountAcct = $this->rmaItem->getDiscountAccount();
        $discountAmount = $undiscountedExtendedPrice - $this->getExtendedPrice();
        $memo = sprintf('%s - %s @ %s%%',
            $customer->getId(),
            $this->getSku(),
            $discountRate * 100
        );
        if ($discountAmount != 0) {
            $glTrans->addEntry($discountAcct, -$discountAmount, $memo);
        }
    }

    /** @return double */
    public function getExtendedPrice()
    {
        return Money::round(
            $this->rmaItem->getFinalUnitPrice() * $this->quantity,
            SalesOrderDetail::EXT_PRECISION);
    }

    public function getTaxRate()
    {
        return $this->rmaItem->getTaxRate();
    }

    public function getAllocations()
    {
        /* Received items do not have any allocations */
        return [];
    }

    public function getQtyUnallocated()
    {
        return $this->getQuantity();
    }

    public function getQtyRemaining()
    {
        return $this->getQuantity();
    }

    public function getQtyAvailableTo(RequirementCollection $requirements)
    {
        $checker = new CompatibilityChecker();
        if (! $checker->areCompatible($this, $requirements)) {
            return 0;
        }
        return $this->getQuantity();
    }

    /** @return string */
    public function getVersion()
    {
        return $this->rmaItem->getVersion();
    }

    public function getCustomization()
    {
        return $this->rmaItem->getCustomization();
    }

    public function getFullSku()
    {
        return $this->rmaItem->getFullSku();
    }

    /** @deprecated */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    public function updateInstructions(StockBin $newBin)
    {
        $intoLoc = $this->receipt->getReceivingLocation();
        $bins = [$newBin];
        $this->instructions->retrieveBinLabels($bins);
        $this->instructions->moveBins($bins, $intoLoc);
    }
}
