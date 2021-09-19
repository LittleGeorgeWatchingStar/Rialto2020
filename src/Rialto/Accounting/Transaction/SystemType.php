<?php

namespace Rialto\Accounting\Transaction;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Database\Orm\DbManager;
use Rialto\Database\Orm\ErpDbManager;
use Rialto\Entity\RialtoEntity;


/**
 * A SystemType identifies the type of an accounting transaction.
 */
class SystemType implements RialtoEntity
{
    /**************\
     STATIC MEMBERS
    \**************/

    const JOURNAL = 0;

    const SALES_INVOICE = 10;
    const CREDIT_NOTE = 11;
    const RECEIPT = 12;
    const CC_AUTHORIZATION = 13;
    const LOCATION_TRANSFER = 16;
    const STOCK_ADJUSTMENT = 17;

    const PURCHASE_INVOICE = 20;
    const DEBIT_NOTE = 21;
    const CREDITOR_PAYMENT = 22;
    const CREDITOR_REFUND = 24;

    /**
     * For receipt of purchase orders.
     */
    const PURCHASE_ORDER_DELIVERY = 25;

    // TODO: The old system uses 25, though in reality it should not share inc w/ purchase order
    const WORK_ORDER_REFERENCE = 25;

    const WORK_ORDER_ISSUE_REVERSAL = 27;
    const WORK_ORDER_ISSUE = 28;

    /**
     * @deprecated Type 26 was used for work order receipts before they
     * were connected to GoodsReceivedNotices. This type is superseded
     * by 25 (for work orders with POs) and 32 (for in-house work orders
     * without POs).
     */
    const WORK_ORDER_RECEIPT_LEGACY = 26;

    /**
     * For receipt of in-house work orders that do not have POs.
     */
    const WORK_ORDER_RECEIPT = 32;


    const COST_UPDATE = 35;
    const CUSTOMER_REFUND = 101;
    const CREDIT_CARD_SWEEP = 102;
    const STOCK_BIN_SERIAL_NUMBER = 200;

    const CUSTOMIZATIONS = 300;

    /**
     * For transfers between bank accounts.
     */
    const BANK_TRANSFER = 400;

    /** @return SystemType */
    public static function fetch($id, ObjectManager $om = null)
    {
        $om = $om ?: ErpDbManager::getInstance();
        $sysType = $om->find(self::class, $id);
        assertion(null !== $sysType, "No such SystemType '$id'");
        return $sysType;
    }

    public static function fetchReceipt(ObjectManager $om = null)
    {
        return self::fetch(self::RECEIPT, $om);
    }

    /** @return SystemType */
    public static function fetchCustomerRefund(ObjectManager $om = null)
    {
        return self::fetch(self::CUSTOMER_REFUND, $om);
    }

    /** @return SystemType */
    public static function fetchCreditNote(ObjectManager $om = null)
    {
        return self::fetch(self::CREDIT_NOTE, $om);
    }

    /** @return SystemType */
    public static function fetchCreditorPayment(ObjectManager $om = null)
    {
        return self::fetch(self::CREDITOR_PAYMENT, $om);
    }

    /** @return SystemType */
    public static function fetchCreditorRefund(ObjectManager $om = null)
    {
        return self::fetch(self::CREDITOR_REFUND, $om);
    }

    /** @return SystemType */
    public static function fetchPurchaseInvoice(ObjectManager $om = null)
    {
        return self::fetch(self::PURCHASE_INVOICE, $om);
    }

    /** @return SystemType */
    public static function fetchDebitNote(DbManager $om)
    {
        return self::fetch(self::DEBIT_NOTE, $om);
    }

    /** @return SystemType */
    public static function fetchCardAuthorization(DbManager $om)
    {
        return self::fetch(self::CC_AUTHORIZATION, $om);
    }

    /** @return SystemType */
    public static function fetchCostUpdate()
    {
        return self::fetch(self::COST_UPDATE);
    }

    /** @return SystemType */
    public static function fetchTransfer()
    {
        return self::fetch(self::LOCATION_TRANSFER);
    }

    public static function fetchPurchaseOrderDelivery()
    {
        return self::fetch(self::PURCHASE_ORDER_DELIVERY);
    }

    public static function fetchSalesInvoice(ObjectManager $om)
    {
        return self::fetch(self::SALES_INVOICE, $om);
    }

    /** @return SystemType */
    public static function fetchStockAdjustment(ObjectManager $om = null)
    {
        return self::fetch(self::STOCK_ADJUSTMENT, $om);
    }

    public static function fetchStockBinSplit(ObjectManager $om = null)
    {
        return self::fetch(self::STOCK_BIN_SERIAL_NUMBER, $om);
    }

    public static function fetchWorkOrderIssue()
    {
        return self::fetch(self::WORK_ORDER_ISSUE);
    }

    public static function fetchCustomizations()
    {
        return self::fetch(self::CUSTOMIZATIONS);
    }

    public static function fetchWorkOrderReceipt()
    {
        return self::fetch(self::WORK_ORDER_RECEIPT);
    }

    public static function fetchWorkOrderReference()
    {
        return self::fetch(self::WORK_ORDER_REFERENCE);
    }

    public static function fetchBankTransfers()
    {
        return self::fetch(self::BANK_TRANSFER);
    }



    /****************\
     INSTANCE MEMBERS
    \****************/

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name = '';

    /**
     * @var int
     */
    private $currentNumber = 0;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function __toString()
    {
        return $this->name;
    }

    /**
     * Returns the TypeID of this type.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the current TypeNo value.
     */
    public function getCurrentNumber(): int
    {
        return $this->currentNumber;
    }

    /**
     * Increments the TypeNo counter and returns the new number.
     */
    public function getNextNumber(): int
    {
        // TODO: not safe for concurrency.
        $this->currentNumber += 1;
        return $this->currentNumber;
    }

    public function isCardAuthorization(): bool
    {
        return $this->isType(self::CC_AUTHORIZATION);
    }

    public function isReceipt(): bool
    {
        return $this->isType(self::RECEIPT);
    }

    public function isSalesInvoice(): bool
    {
        return $this->isType(self::SALES_INVOICE);
    }

    public function isCreditNote(): bool
    {
        return $this->isType(self::CREDIT_NOTE);
    }

    public function isTransfer(): bool
    {
        return $this->isType(self::LOCATION_TRANSFER);
    }

    public function isType($type): bool
    {
        return $this->id == $type;
    }
}
