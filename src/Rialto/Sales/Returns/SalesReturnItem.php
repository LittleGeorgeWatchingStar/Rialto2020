<?php

namespace Rialto\Sales\Returns;

use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Money;
use Rialto\Database\Orm\DbManager;
use Rialto\Database\Orm\ErpDbManager;
use Rialto\Entity\RialtoEntity;
use Rialto\IllegalStateException;
use Rialto\Manufacturing\Customization\Customization;
use Rialto\Manufacturing\WorkOrder\Orm\WorkOrderRepository;
use Rialto\Manufacturing\WorkOrder\WorkOrder;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Invoice\InvoiceableOrderItem;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\Order\SalesOrderDetail;
use Rialto\Sales\Price\Orm\ProductPriceRepository;
use Rialto\Sales\Price\PriceCalculator;
use Rialto\Sales\Price\ProductPrice;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Facility\Facility;
use Rialto\Stock\Item\StockItem;
use Rialto\Stock\Item\Version\Version;
use Rialto\Stock\Move\StockMove;
use Rialto\Stock\VersionedItem;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use UnexpectedValueException;

class SalesReturnItem implements
    RialtoEntity,
    InvoiceableOrderItem,
    VersionedItem
{
    const DISP_CUSTOMER = 'customer';
    const DISP_STOCK = 'stock';
    const DISP_ENGINEERING = 'engineering';
    const DISP_MANUFACTURER = 'manufacturer';
    const DISP_SUPPLIER = 'supplier';
    const DISP_DISCARD = 'discard';

    /**
     * @var integer
     */
    private $id;

    /**
     * @var integer
     * @Assert\NotBlank()
     * @Assert\Type(type="integer", message="not an int")
     * @Assert\Range(min=0)
     */
    private $qtyAuthorized = 0;

    /** @var integer */
    private $qtyReceived = 0;

    /** @var integer */
    private $qtyPassed = 0;

    /** @var integer */
    private $qtyFailed = 0;
    /**
     * @var string
     */
    private $passDisposition = self::DISP_CUSTOMER;

    /**
     * @var string
     */
    private $failDisposition = self::DISP_DISCARD;

    /** @var StockMove */
    private $originalStockMove;

    /**
     * @var SalesReturn
     */
    private $salesReturn;

    /**
     * @var WorkOrder
     * The work order in which the returned item was created.
     */
    protected $originalWorkOrder;

    protected $reworkOrder = null;

    public function __construct(SalesReturn $salesReturn, StockMove $move)
    {
        $this->salesReturn = $salesReturn;
        $this->originalStockMove = $move;
        assertion($this->getQtyInvoiced() > 0);
    }

    public function loadOriginalProducer(DbManager $dbm)
    {
        if ($this->originalWorkOrder) {
            return;
        }
        if (!$this->isManufactured()) {
            return;
        }
        $originalBin = $this->originalStockMove->getStockBin();
        if ($originalBin) {
            /** @var $repo WorkOrderRepository */
            $repo = $dbm->getRepository(WorkOrder::class);
            $this->originalWorkOrder = $repo->findOriginalProducer($originalBin);
        }
    }

    public function getEngineerBranch()
    {
        return $this->salesReturn->getEngineerBranch();
    }

    /**
     * @return string[]
     *  A list of possible actions to take if the returned item
     *  passes testing.
     *
     * @see getValidFailDispositions
     */
    public static function getValidPassDispositions(StockItem $item)
    {
        return [
            'Return to customer' => self::DISP_CUSTOMER,
            'Return to stock' => self::DISP_STOCK,
            'Send to engineering' => self::DISP_ENGINEERING,
        ];
    }

    /**
     * @return string[]
     *  A list of possible actions to take if the returned item
     *  fails testing.
     *
     * @see getValidPassDispositions
     */
    public static function getValidFailDispositions(StockItem $item)
    {
        $returnType = $item->isManufactured() ?
            self::DISP_MANUFACTURER :
            self::DISP_SUPPLIER;
        $returnLabel = sprintf('Return to %s', $returnType);

        return [
            'Discard' => self::DISP_DISCARD,
            $returnLabel => $returnType,
            'Send to engineering' => self::DISP_ENGINEERING,
        ];
    }

    /**
     * Get QtyAuthorized
     *
     * @return integer
     */
    public function getQtyAuthorized()
    {
        return $this->qtyAuthorized;
    }

    public function setQtyAuthorized($qty)
    {
        $this->qtyAuthorized = $qty;
        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validateQtyAuthorized(ExecutionContextInterface $context)
    {
        if ($this->qtyAuthorized > $this->getQtyInvoiced()) {
            $context->buildViolation(
                "Cannot authorize more than were originally invoiced.")
                ->atPath('qtyAuthorized')
                ->addViolation();
        } elseif ($this->qtyAuthorized < $this->qtyReceived) {
            $context->buildViolation(
                "Cannot reduce quantity authorized to less than has already been received.")
                ->atPath('qtyAuthorized')
                ->addViolation();
        }
    }

    public function getQtyReceived()
    {
        return $this->qtyReceived;
    }

    public function addQtyReceived($qty)
    {
        $this->qtyReceived += $qty;
    }

    public function getQtyPassed()
    {
        return $this->qtyPassed;
    }

    public function addQtyPassed($qty)
    {
        $this->qtyPassed += $qty;
    }

    public function getQtyFailed()
    {
        return $this->qtyFailed;
    }

    public function addQtyFailed($qty)
    {
        $this->qtyFailed += $qty;
    }

    public function getQtyTested()
    {
        return $this->qtyPassed + $this->qtyFailed;
    }

    /**
     * Set PassDisposition
     *
     * @param $passDisposition
     */
    public function setPassDisposition($passDisposition)
    {
        $this->passDisposition = $passDisposition;
    }

    /**
     * Get PassDisposition
     *
     * @return
     */
    public function getPassDisposition()
    {
        return $this->passDisposition;
    }

    /**
     * Set FailDisposition
     *
     * @param $failDisposition
     */
    public function setFailDisposition($failDisposition)
    {
        $this->failDisposition = $failDisposition;
    }

    /**
     * Get FailDisposition
     *
     * @return
     */
    public function getFailDisposition()
    {
        return $this->failDisposition;
    }

    /**
     * @return WorkOrder|null
     *  Returns null if this item is not manufactured.
     */
    public function getOriginalWorkOrder()
    {
        return $this->originalWorkOrder;
    }

    /** @Assert\Callback */
    public function validateFailDisposition(ExecutionContextInterface $context)
    {
        if (($this->failDisposition == self::DISP_MANUFACTURER)
            && (!$this->originalWorkOrder)
        ) {
            $context->buildViolation(
                "Cannot return for rework if the original work order is not known.")
                ->atPath('failDisposition')
                ->addViolation();
        }
    }

    /** @Assert\Callback */
    public function validateEngineerBranch(ExecutionContextInterface $context)
    {
        if ($this->hasEngineerDisposition() && !$this->getEngineerBranch()) {
            $context->buildViolation(
                "A engineer branch must be set when choosing a engineer disposition.")
                ->atPath('failDisposition')
                ->addViolation();
        }
    }

    private function hasEngineerDisposition()
    {
        return $this->failDisposition == self::DISP_ENGINEERING ||
            $this->passDisposition == self::DISP_ENGINEERING;
    }

    /**
     * @param WorkOrder $order
     * @throws IllegalStateException
     *  If this item is not manufactured.
     */
    public function setReworkOrder(WorkOrder $order)
    {
        if ($this->isManufactured()) {
            $this->reworkOrder = $order;
        } else {
            throw new IllegalStateException(
                'Cannot set work order for a purchased item'
            );
        }
    }

    /**
     * @return WorkOrder|null
     */
    public function getReworkOrder()
    {
        return $this->reworkOrder;
    }

    public function hasReworkOrder()
    {
        return null !== $this->reworkOrder;
    }

    /**
     * @return Facility
     * @throws IllegalStateException
     *  If this item is not manufactured.
     */
    public function getLocationManufactured()
    {
        return $this->originalWorkOrder->getLocation();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /** @return StockItem */
    public function getStockItem()
    {
        return $this->originalStockMove->getStockItem();
    }

    /** @return string */
    public function getSku()
    {
        return $this->originalStockMove->getSku();
    }

    public function getStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getSku();
    }

    /** @return double */
    public function getStandardCost()
    {
        return $this->getStockItem()->getStandardCost();
    }

    /**
     * Get salesReturn
     *
     * @return SalesReturn
     */
    public function getSalesReturn()
    {
        return $this->salesReturn;
    }

    /** @return DebtorTransaction */
    public function getOriginalInvoice()
    {
        return $this->salesReturn->getOriginalInvoice();
    }

    public function getQtyInvoiced()
    {
        return -$this->originalStockMove->getQuantity();
    }

    /** @return StockMove */
    public function getOriginalStockMove()
    {
        return $this->originalStockMove;
    }

    public function isForStockMove(StockMove $move)
    {
        return $this->originalStockMove->equals($move);
    }

    /**
     * The stock bin from which this item was originally shipped.
     * @return StockBin
     */
    public function getOriginalStockBin()
    {
        $move = $this->getOriginalStockMove();
        return $move->getStockBin();
    }

    /**
     * @inheritdoc
     */
    public function getBaseUnitPrice()
    {
        $origLineItem = $this->getOriginalLineItem();
        if ($origLineItem->isAssembly()) {
            /* $this->stockItem is just one of the items in the original
             * assembly, so we need to look up its original price. */
            $dbm = ErpDbManager::getInstance();
            /** @var $repo ProductPriceRepository */
            $repo = $dbm->getRepository(ProductPrice::class);
            return Money::round(
                $repo->findBySalesReturnItem($this),
                SalesOrderDetail::UNIT_PRECISION);
        } else {
            return $origLineItem->getBaseUnitPrice();
        }
    }

    public function getPriceAdjustment()
    {
        return $this->getOriginalLineItem()->getPriceAdjustment();
    }

    /**
     * @inheritdoc
     */
    public function getFinalUnitPrice()
    {
        $calculator = new PriceCalculator(SalesOrderDetail::UNIT_PRECISION);
        return $calculator->calculateFinalUnitPrice($this);
    }

    /**
     * @inheritdoc
     */
    public function getExtendedPrice()
    {
        return Money::round(
            $this->getFinalUnitPrice() * $this->qtyAuthorized,
            SalesOrderDetail::EXT_PRECISION);
    }

    public function getDiscountRate()
    {
        $origLineItem = $this->getOriginalLineItem();
        return $origLineItem->getDiscountRate();
    }

    public function getDiscountAccount()
    {
        return GLAccount::fetchSalesReturn();
    }

    public function getTaxRate()
    {
        $origLineItem = $this->getOriginalLineItem();
        return $origLineItem->getTaxRate();
    }

    /** @return SalesOrderDetail */
    private function getOriginalLineItem()
    {
        $origOrder = $this->getSalesOrder();

        if ($origOrder->containsItem($this)) {
            return $origOrder->getLineItem($this);
        } else {
            /* The original line item was for an assembly; this
             * item is just one of the components in the assembly. */
            $move = $this->getOriginalStockMove();
            if (!$move->hasParentItem()) {
                throw new UnexpectedValueException(
                    "SalesReturnItem has no parent item"
                );
            }
            $parentItem = $move->getParentItem();
            return $origOrder->getLineItem($parentItem);
        }
    }

    /**
     * @return SalesOrder
     */
    public function getSalesOrder()
    {
        return $this->salesReturn->getOriginalOrder();
    }

    /**
     * The item in the replacement order (if any) to replace this item.
     *
     * @return SalesOrderDetail|null
     */
    public function getReplacementOrderItem()
    {
        $order = $this->salesReturn->getReplacementOrder();
        if (!$order) {
            return null;
        }
        if ($order->containsItem($this)) {
            return $order->getLineItem($this);
        }
        return null;
    }

    /** @return Customer */
    public function getCustomer()
    {
        $invoice = $this->getOriginalInvoice();
        return $invoice->getCustomer();
    }

    public function isControlled()
    {
        return $this->getStockItem()->isControlled();
    }

    public function isManufactured()
    {
        return $this->getStockItem()->isManufactured();
    }

    /** @return Version */
    public function getVersion()
    {
        if ($this->isManufactured() && $this->originalWorkOrder) {
            return $this->originalWorkOrder->getVersion();
        }
        $version = $this->getOriginalStockMove()->getVersion();
        if (!$version->isSpecified()) {
            $version = $this->getVersionOrdered();
        }
        if (!$version->isSpecified()) {
            $version = $this->getStockItem()->getShippingVersion();
        }
        return $version;
    }

    public function getFullSku()
    {
        $code = $this->getSku();
        $version = $this->getVersion() ?: Version::none();
        $code .= $version->getStockCodeSuffix();
        return $code;
    }

    /** @deprecated */
    public function getVersionedStockCode()
    {
        trigger_error(__METHOD__, E_USER_DEPRECATED);
        return $this->getFullSku();
    }

    /**
     * @return Version
     *  The version of this item that the customer originally ordered.
     */
    private function getVersionOrdered()
    {
        $origLineItem = $this->getOriginalLineItem();
        if ($origLineItem->isAssembly()) {
            $bom = $origLineItem->getBom();
            $bomItem = $bom->getItem($this);
            return $bomItem ?
                $bomItem->getVersion() :
                $this->getStockItem()->getShippingVersion();
        } else {
            return $origLineItem->getVersion();
        }
    }

    /** @return Customization|null */
    public function getCustomization()
    {
        return $this->originalStockMove->getCustomization();
    }

    /**
     * COGS = cost of goods sold
     * @return GLAccount
     */
    public function getCogsAccount()
    {
        $lineItem = $this->getOriginalLineItem();
        return $lineItem->getCogsAccount();
    }

    /** @return GLAccount */
    public function getStockAccount()
    {
        return $this->getStockItem()->getStockAccount();
    }

    /** @return GLAccount */
    public function getSalesAccount()
    {
        $lineItem = $this->getOriginalLineItem();
        return $lineItem->getSalesAccount();
    }
}
