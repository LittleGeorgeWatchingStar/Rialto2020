<?php

namespace Rialto\Accounting\Ledger\Account;

use Doctrine\Common\Persistence\ObjectManager;
use Rialto\Database\DatabaseException;
use Rialto\Database\Orm\ErpDbManager;
use Rialto\Entity\RialtoEntity;
use Rialto\Sales\Customer\SalesArea;
use Rialto\Sales\GLPosting\GLPosting;
use Rialto\Sales\Type\SalesType;
use Rialto\Stock\Category\StockCategory;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A general ledger (GL) account.
 *
 * All financial transactions are movements of money between GL accounts.
 */
class GLAccount implements RialtoEntity
{
    /**************\
     STATIC MEMBERS
    \**************/

    const REGULAR_CHECKING_ACCOUNT = 10200;
    const WGK_PAYMENT_ACCOUNT = 10300;
    const HSBC_CHECKING_ACCOUNT = 10400;
    const AUTHORIZE_NET = 10600;
    const ACCOUNTS_RECEIVABLE = 11000;

    const RAW_INVENTORY = 12000;
    const WORK_IN_PROCESS_INVENTORY = 12100;
    const FINISHED_INVENTORY = 12500;

    const ACCOUNTS_PAYABLE = 20000;
    const UNINVOICED_INVENTORY = 20100;
    const ACCRUED_TRANSACTION_FEES = 21000;
    const PREPAID_REVENUE = 22000;

    const CHARGE_TO_ENGINEERING = 48200;
    const CHARGE_TO_MARKETING = 48500;
    const SALES_RETURN = 48000;
    const SALES_DISCOUNTS = 49000;

    const DIRECT_LABOUR = 57000;
    const MATERIALS_COST = 57200;
    const SHIPPING_EXPENSES = 57500;
    const INVENTORY_ADJUSTMENTS = 58500;
    const PURCHASE_VARIANCE = 59000;
    const PURCHASE_DISCOUNTS = 59500;

    const ADVERTISING_EXPENSE = 60000;
    const BANK_CHARGES = 62000;
    const DEVELOPMENT_EXPENSE = 68000;

    const OFFICE_EXPENSE = 71000;
    const SALES_TAXES = 71500;

    const WARRANTY_EXPENSE = 89000;

    /**
     * Fetches the GLAccount object whose AccountCode is given.
     *
     * @static
     * @param int $code
     * @return GLAccount
     */
    public static function fetch($code, ObjectManager $om = null)
    {
        $om = $om ?: ErpDbManager::getInstance();
        $account = $om->find(self::class, $code);
        assertion(null !== $account, "No such account $code");
        return $account;
    }

    /**
     * @static
     * @return GLAccount
     */
    public static function fetchAccountsPayable(ObjectManager $om = null)
    {
        return self::fetch(self::ACCOUNTS_PAYABLE, $om);
    }

    /**
     * @static
     * @return GLAccount
     */
    public static function fetchAccountsReceivable()
    {
        return self::fetch(self::ACCOUNTS_RECEIVABLE);
    }

    /**
     * Returns the account for Authorize.net transactions.
     *
     * @static
     * @return GLAccount
     */
    public static function fetchAuthorizeNet(ObjectManager $om = null)
    {
        return self::fetch(self::AUTHORIZE_NET, $om);
    }

    /**
     * Returns the account for recording bank charges.
     *
     * @static
     * @return GLAccount
     */
    public static function fetchBankCharges()
    {
        return self::fetch(self::BANK_CHARGES);
    }

    /**
     * @return GLAccount
     */
    public static function fetchCogsAccount(
        SalesArea $area,
        StockCategory $cat,
        SalesType $type)
    {
        $posting = GLPosting::fetchCogsPosting($area, $cat, $type);
        if (!$posting) throw new DatabaseException(sprintf(
            'No CogsGLPosting to match area %s, category %s, and type %s',
            $area->getId(), $cat->getId(), $type->getId()
        ));
        return $posting->getAccount();
    }

    /** @return GLAccount */
    public static function fetchSalesReturn()
    {
        return self::fetch(self::SALES_RETURN);
    }

    /** @return GLAccount */
    public static function fetchSalesTaxes()
    {
        return self::fetch(self::SALES_TAXES);
    }

    /**
     * Returns the default account for sales price adjustments (eg, sales
     * discounts).
     *
     * @static
     * @return GLAccount
     */
    public static function fetchDefaultSalesAdjustment()
    {
        return self::fetch(self::SALES_DISCOUNTS);
    }

    /** @return GLAccount */
    public static function fetchShippingExpenses(ObjectManager $om = null)
    {
        return self::fetch(self::SHIPPING_EXPENSES, $om);
    }

    /** @return GLAccount */
    public static function fetchDevelopmentExpense(ObjectManager $om = null)
    {
        return self::fetch(self::DEVELOPMENT_EXPENSE, $om);
    }

    /** @return GLAccount */
    public static function fetchRawInventory()
    {
        return self::fetch(self::RAW_INVENTORY);
    }

    /** @return GLAccount */
    public static function fetchFinishedInventory()
    {
        return self::fetch(self::FINISHED_INVENTORY);
    }

    /** @return GLAccount */
    public static function fetchUninvoicedInventory()
    {
        return self::fetch(self::UNINVOICED_INVENTORY);
    }

    /**
     * @return GLAccount
     */
    public static function fetchSalesAccount(
        SalesArea $area,
        StockCategory $cat,
        SalesType $type)
    {
        $posting = GLPosting::fetchSalesPosting($area, $cat, $type);
        if ($posting) {
            return $posting->getSalesAccount();
        }
        throw new DatabaseException(
            "No SalesGLPosting to match area $area, category $cat, and type $type");
    }

    /**
     * @static
     * @return GLAccount
     */
    public static function fetchMaterialsCost()
    {
        return self::fetch(self::MATERIALS_COST);
    }

    /**
     * @static
     * @return GLAccount
     */
    public static function fetchPrepaidRevenue(ObjectManager $om = null)
    {
        return self::fetch(self::PREPAID_REVENUE, $om);
    }

    /**
     * @static
     * @return GLAccount
     */
    public static function fetchAccruedTransactionFees(ObjectManager $om = null)
    {
        return self::fetch(self::ACCRUED_TRANSACTION_FEES, $om);
    }

    /**
     * @static
     * @return GLAccount
     */
    public static function fetchWorkInProcess(ObjectManager $om = null)
    {
        return self::fetch(self::WORK_IN_PROCESS_INVENTORY, $om);
    }

    /** @return GLAccount */
    public static function fetchWarrantyExpense()
    {
        return self::fetch(self::WARRANTY_EXPENSE);
    }

    /** @return GLAccount */
    public static function fetchSalesDiscounts(ObjectManager $om = null)
    {
        return self::fetch(self::SALES_DISCOUNTS, $om);
    }

    /** @return GLAccount */
    public static function fetchPurchaseVariance()
    {
        return self::fetch(self::PURCHASE_VARIANCE);
    }


    /****************\
     INSTANCE MEMBERS
    \****************/

    /** @var int */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank(message="Account name is required.")
     * @Assert\Length(max="50")
     */
    private $name = '';

    /** @var AccountGroup */
    private $accountGroup;

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function equals(GLAccount $other)
    {
        return $other->getId() == $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = trim($name);
    }

    public function getLabel(): string
    {
        return sprintf('%s - %s', $this->id, $this->name);
    }

    public function __toString()
    {
        return $this->getLabel();
    }

    /** @return string */
    public function getGroupName()
    {
        return $this->accountGroup->getName();
    }

    /** @return string */
    public function getSectionName()
    {
        return $this->accountGroup->getSectionName();
    }

    public function isProfitAndLoss()
    {
        return $this->accountGroup->isProfitAndLoss();
    }

    public function getSignForReporting()
    {
        return $this->accountGroup->getSignForReporting();
    }

    public function getAccountGroup()
    {
        return $this->accountGroup;
    }

    public function setAccountGroup($group)
    {
        $this->accountGroup = $group;
    }
}
