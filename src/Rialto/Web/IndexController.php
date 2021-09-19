<?php

namespace Rialto\Web;

use DateTime;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Period\Orm\PeriodRepository;
use Rialto\Accounting\Period\Period;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Database\Orm\DbManager;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Security\Role\Role;
use Rialto\Security\User\User;
use Rialto\Stock\Sku;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;


/**
 * Renders the main index page.
 */
class IndexController extends RialtoController
{
    private $user = null;

    /**
     * The Rialto main index page.
     *
     * @Route("/", name="index")
     */
    public function indexAction()
    {
        $this->user = $this->getCurrentUser();

        if ($this->isGranted(Role::EMPLOYEE)) {
            return $this->showEmployeeMenu();
        } elseif ($this->isGranted(Role::SUPPLIER_SIMPLE)) {
            $supplier = $this->user->getSupplier();
            if ($supplier) {
                $uri = $this->generateUrl('supplier_order_list', [
                    'id' => $supplier->getId(),
                ]);
                return $this->redirect($uri);
            }
        }
        throw $this->forbidden();
    }

    private function showEmployeeMenu()
    {
        $tabs = $this->getTabs();
        $tab = $this->getCurrentTab($tabs);

        return $this->render('core/index/index.html.twig', [
            'tabs' => $this->renderTabs($tabs),
            'currentTab' => $tab->getTabName(),
            'transactionLinks' => $tab->getTransactionLinks(),
            'reportsLinks' => $tab->getReportLinks(),
            'maintenanceLinks' => $tab->getMaintenanceLinks(),
        ]);
    }

    /** @return IndexTab[] */
    private function getTabs()
    {
        /** @var $router RouterInterface */
        $router = $this->get(RouterInterface::class);
        /** @var $auth AuthorizationCheckerInterface */
        $auth = $this->get(AuthorizationCheckerInterface::class);
        $sentryDsn = $this->container->getParameter('sentry_dsn');
        return [
            new SalesTab($router, $auth),
            new ReceivablesTab($router, $auth),
            new PayablesTab($router, $auth),
            new PurchasingTab($router, $auth),
            new InventoryTab($router, $auth),
            new ManufacturingTab($router, $auth),
            new GeneralLedgerTab($router, $auth, $this->dbm),
            new SetupTab($router, $auth, $sentryDsn),
        ];
    }

    /**
     * @param IndexTab[] $tabs
     * @return string[]
     */
    private function renderTabs(array $tabs)
    {
        $router = $this->get(RouterInterface::class);
        $rendered = [];
        foreach ($tabs as $tab) {
            if (!$tab->isVisibleToUser($this->user)) {
                continue;
            }
            $label = $tab->getTabName();
            $uri = $router->generate('index', [
                'Application' => $tab->getKey()
            ]);
            $rendered[$label] = $uri;
        }
        return $rendered;
    }

    /**
     * @param IndexTab[] $tabs
     * @return IndexTab
     */
    private function getCurrentTab(array $tabs)
    {
        $key = $this->getCurrentKey();
        foreach ($tabs as $tab) {
            if ($tab->matches($key, $this->user)) {
                return $tab;
            }
        }

        foreach ($tabs as $tab) {
            if ($tab->isVisibleToUser($this->user)) {
                return $tab;
            }
        }
        throw $this->forbidden();
    }

    private function getCurrentKey()
    {
        if (isset($_GET['Application'])) {
            $_SESSION['currentTab'] = $_GET['Application'];
            return $_GET['Application'];
        } elseif (isset($_SESSION['currentTab'])) {
            return $_SESSION['currentTab'];
        } else {
            return SalesTab::KEY;
        }
    }
}


interface IndexTab
{
    public function getTabName();

    public function getKey();

    public function isVisibleToUser(User $user);

    public function matches($key, User $user);

    public function getTransactionLinks();

    public function getReportLinks();

    public function getMaintenanceLinks();
}


abstract class AbstractTab implements IndexTab
{
    /** @var RouterInterface */
    private $router;

    /** @var AuthorizationCheckerInterface */
    private $authorization;

    public function __construct(RouterInterface $router,
                                AuthorizationCheckerInterface $auth)
    {
        $this->router = $router;
        $this->authorization = $auth;
    }

    public function getKey()
    {
        return static::KEY;
    }

    public function matches($key, User $user)
    {
        return ($this->getKey() == $key) &&
            ($this->isVisibleToUser($user));
    }

    protected function relativeDate($string)
    {
        return date('Y-m-d', strtotime($string));
    }

    protected function isGranted($role)
    {
        return $this->authorization->isGranted($role);
    }

    protected function generateUrl($routeName, array $params = [])
    {
        return $this->router->generate($routeName, $params);
    }

    protected function urlIfGranted($role, $routeName, array $params = [])
    {
        return $this->isGranted($role)
            ? $this->generateUrl($routeName, $params)
            : null;
    }
}


class SalesTab extends AbstractTab
{
    const KEY = 'order';

    public function getTabName()
    {
        return 'Sales';
    }

    public function isVisibleToUser(User $user)
    {
        return $this->isGranted(Role::CUSTOMER_SERVICE);
    }

    public function getTransactionLinks()
    {
        $links = [];
        if ($this->isGranted(Role::SALES)) {
            $links["Create new Sales Order or Quote"] = $this->generateUrl(
                'sales_order_create'
            );
            $links["Batch update customer branches"] = $this->generateUrl(
                'sales_batch_update_branch'
            );
        }

        return $links;
    }

    public function getReportLinks()
    {
        $links = [];

        $links["Outstanding sales orders"] = $this->generateUrl('sales_order_list', [
            'salesStage' => SalesOrder::ORDER,
            'shipped' => 'no',
        ]);

        $links["All quotes"] = $this->generateUrl('sales_order_list', [
            'salesStage' => SalesOrder::QUOTATION
        ]);

        $links["Unprinted budgetary quotations"] = $this->generateUrl('sales_order_list', [
            'salesStage' => SalesOrder::BUDGET,
            'shipped' => 'no',
            'printed' => 'no',
        ]);

        $links["Shipped orders"] = $this->generateUrl('sales_order_list', [
            'shipped' => 'yes',
            'startDate' => $this->relativeDate('-1 year'),
        ]);

        $links["Countdown clock"] = $this->generateUrl('target_ship_date_dashboard');

        $links['Customer inquiry'] = $this->generateUrl('customer_list');
        if ($this->isGranted(Role::SALES)) {
            $links['Customer contact info'] = $this->generateUrl('Core_Audit_report', [
                'module' => 'Sales',
                'auditName' => 'CustomerContactInfo',
            ]);
        }
        $links["Product status"] = $this->generateUrl('Sales_SalesStats');
        if ($this->isGranted(Role::STOCK)) {
            $links["Stock consumption (new)"] = $this->generateUrl('Core_Audit_report', [
                'module' => 'Stock',
                'auditName' => 'StockConsumption',
            ]);
            $links["Stock consumption (old)"] = $this->generateUrl(
                'Stock_StockConsumption'
            );
        }
        $links["Sales by period"] = $this->generateUrl('Core_Audit_report', [
            'module' => 'Sales',
            'auditName' => 'SalesByPeriod',
        ]);
        $links["Customer product purchases"] = $this->generateUrl('Core_Audit_report', [
            'module' => 'Sales',
            'auditName' => 'CustomerProductPurchases',
        ]);
        $links["Sales commission"] = $this->generateUrl('Core_Audit_report', [
            'module' => 'Sales',
            'auditName' => 'Commission',
        ]);

        $links["Customers by state"] = $this->generateUrl('Core_Audit_report', [
            'module' => 'Sales',
            'auditName' => 'CustomersByState',
        ]);

        $links["Revenue by item"] = $this->generateUrl('Core_Audit_report', [
            'module' => 'Sales',
            'auditName' => 'RevenueByItem',
        ]);

        $links['RMA statistics'] = $this->generateUrl('Core_Audit_report', [
            'module' => 'Sales',
            'auditName' => 'RmaStats',
        ]);

        return $links;
    }

    public function getMaintenanceLinks()
    {
        $links = [];
        if ($this->isGranted(Role::SALES)) {
            $links['Create a customer'] = $this->generateUrl('Sales_Customer_create');
            $links["Discount schedules"] = $this->generateUrl('discount_group_list');
        }
        if ($this->isGranted(Role::ADMIN)) {
            $links["Sales configuration"] = $this->generateUrl('rialto_sales_config');
            $links['Sales tax regimes'] = $this->generateUrl('tax_regime_list');
            $links["Shopify storefronts"] = $this->generateUrl('shopify_storefront_list');
            $links["Magento 2 storefronts"] = $this->generateUrl('magento2_storefront_list');
        }

        return $links;
    }

}

class ReceivablesTab extends AbstractTab
{
    const KEY = 'AR';
    const DB_INDEX = 1;

    public function getTabName()
    {
        return 'Receivables';
    }

    public function isVisibleToUser(User $user)
    {
        return $this->isGranted(Role::ACCOUNTING);
    }

    public function getTransactionLinks()
    {
        $selectOrderToInvoice = $this->generateUrl('sales_order_list');
        $createDebtorTransaction = $this->generateUrl('debtor_transaction_create');
        $allocateReceiptsOrCreditNotes = $this->generateUrl(
            'debtor_transaction_match'
        );
        $transactionsLinks = [
            "Select Order to Invoice" => $selectOrderToInvoice,
            "Create Debtor transaction from File" => $createDebtorTransaction,
            "Allocate Receipts or Credit Notes" => $allocateReceiptsOrCreditNotes,
        ];
        return $transactionsLinks;
    }

    public function getReportLinks()
    {
        $customerTransactionInquiries = $this->generateUrl('customer_select');

        $transactionInquiries = $this->generateUrl('debtor_transaction_list', [
            'startDate' => $this->relativeDate('-6 months'),
        ]);

        $reportLinks = [
            "Customer Transaction Inquiries" => $customerTransactionInquiries,
            "Transaction Inquiries" => $transactionInquiries,
        ];

        return $reportLinks;
    }

    public function getMaintenanceLinks()
    {
        $addANewCustomer = $this->generateUrl('Sales_Customer_create');
        $modifyCustomers = $this->generateUrl('customer_select');

        $maintenanceLinks = [
            "Add a New Customer" => $addANewCustomer,
            "Modify Customers" => $modifyCustomers
        ];

        return $maintenanceLinks;
    }

}

class PayablesTab extends AbstractTab
{
    const KEY = 'AP';

    public function getTabName()
    {
        return 'Payables';
    }

    public function isVisibleToUser(User $user)
    {
        return $this->isGranted(Role::ACCOUNTING);
    }

    public function getTransactionLinks()
    {
        return [
            "Approve Invoices" => $this->generateUrl('Purchasing_SupplierInvoice_approvalList'),
            "Print Customer Refunds" => $this->generateUrl('Accounting_printCheques', [
                'type' => SystemType::CUSTOMER_REFUND,
            ]),
            "Review payables for holds" => $this->generateUrl('Creditor_InvoiceHolds'),
            "Process due payables" => $this->generateUrl('Creditor_PaymentRun'),
            "Print cheques" => $this->generateUrl('Accounting_printCheques'),
            "Read emails" => $this->generateUrl('supplier_email_list'),
            "UPS invoices" => $this->generateUrl('ups_invoice_list'),
        ];
    }

    public function getReportLinks()
    {
        $reportLinks = [
            "Supplier transactions" => $this->generateUrl('supplier_transaction_list', [
                'dates' => ['start' => $this->relativeDate('-1 month')],
            ]),
            "Uninvoiced POs" => $this->generateUrl('Core_Audit_report', [
                'module' => 'Accounting',
                'auditName' => 'UninvoicedPOs',
            ]),
            "Uninvoiced GRNs" => $this->generateUrl('grn_list', [
                'invoiced' => 'no',
                'startDate' => $this->relativeDate('-1 year'),
            ]),
            "Uninvoiced Inventory" => $this->generateUrl('Core_Audit_report', [
                'module' => 'Accounting',
                'auditName' => 'UninvoicedInventory',
            ]),
            "Bad supplier transactions" => $this->generateUrl('Core_Audit_report', [
                'module' => 'Accounting',
                'auditName' => 'BadSupplierTransaction',
                '_order' => 'amount',
            ])
        ];

        return $reportLinks;
    }

    public function getMaintenanceLinks()
    {
        $links = [];
        $links["Add a new supplier"] = $this->generateUrl('supplier_create');
        $links["Modify or delete a supplier"] = $this->generateUrl('supplier_select');

        if ($this->isGranted(Role::ADMIN)) {
            $links["Review recurring transactions"] = $this->generateUrl('recurring_invoice_list');
        }

        return $links;
    }

}

class PurchasingTab extends AbstractTab
{
    const KEY = 'AO';
    const DB_INDEX = 3;

    public function getTabName()
    {
        return 'Purchasing';
    }

    public function isVisibleToUser(User $user)
    {
        return $this->isGranted(Role::PURCHASING_DATA);
    }

    public function getTransactionLinks()
    {
        $links = [];
        if ($this->isGranted(Role::PURCHASING)) {
            $links["Purchase Order Entry"] = $this->generateUrl(
                'Purchasing_PurchaseOrder_create'
            );
            $links["Outstanding Purchase Orders"] = $this->generateUrl(
                'purchase_order_list', [
                    'completed' => 'no'
                ]
            );
        }
        return $links;
    }

    public function getReportLinks()
    {
        $links = [];
        if ($this->isGranted(Role::PURCHASING)) {
            $links["Purchase Order Inquiry"] = $this->generateUrl('purchase_order_list');
            $links["Outstanding RFQs"] = $this->generateUrl('rfq_list', [
                'received' => 'no',
            ]);
            $links["PCBs without purchasing data"] = $this->generateUrl('Core_Audit_report', [
                'module' => 'Purchasing',
                'auditName' => 'MissingPurchasingData',
            ]);
            $links["PCB Fab Purchase Order Report"] = $this->generateUrl('Core_Audit_report', [
                'module' => 'Purchasing',
                'auditName' => 'PcbFabOrderReport',
            ]);
        }
        if ($this->isGranted(Role::CUSTOMER_SERVICE)) {
            $links["CMRT summary"] = $this->generateUrl('Core_Audit_report', [
                'module' => 'Purchasing',
                'auditName' => 'CmrtReport',
            ]);
        }
        return $links;
    }

    public function getMaintenanceLinks()
    {
        $links = [];
        $links["Purchasing data"] = $this->generateUrl('purchasing_data_list', [
            'active' => 'yes',
        ]);

        if ($this->isGranted(Role::PURCHASING)) {
            $links["Add a new supplier"] = $this->generateUrl('supplier_create');
            $links["Modify or delete a supplier"] = $this->generateUrl('supplier_select');

            $links["Purchasing data templates"] = $this->generateUrl('purchasing_datatemplate_list');
        }
        if ($this->isGranted(Role::ADMIN)) {
            $links["Supplier APIs"] = $this->generateUrl('Purchasing_SupplierApi_edit');
        }

        $links["Manufacturers"] = $this->generateUrl('part_manufacturer_list', [
            '_limit' => 0,
        ]);

        return $links;
    }

}

class InventoryTab extends AbstractTab
{
    const KEY = 'stock';

    public function getTabName()
    {
        return 'Inventory';
    }

    public function isVisibleToUser(User $user)
    {
        return $this->isGranted(Role::STOCK_VIEW);
    }

    public function getTransactionLinks()
    {
        $links = [];
        if ($this->isGranted(Role::STOCK)) {
            $links["Check in returned parts"] = $this->generateUrl('stock_returns_enter');
            $links["Incomplete returns"] = $this->generateUrl('stock_returns_outstanding');

            $links["Reverse Goods Received"] = $this->generateUrl('grn_list', [
                'startDate' => $this->relativeDate('-1 month'),
            ]);
            $links["Find missing transfer items"] = $this->generateUrl(
                'Stock_Transfer_missingItems'
            );
            $links["Request a stock count"] = $this->generateUrl('stockcount_request');
            $links["Upload a stock count CSV"] = $this->generateUrl('stock_count_upload');
        }

        return $links;
    }

    public function getReportLinks()
    {
        $reportLinks = [
            "Show all stock" => $this->generateUrl('Stock_AllStock'),
            "Stock transfer reports" => $this->generateUrl('stock_transfer_list', [
                'startDate' => date('Y-m-d', strtotime('-6 months')),
            ]),
            "Inventory Valuation Report" => $this->urlIfGranted(Role::STOCK, 'Stock_InventoryValuation'),
            "Outstanding WIP by work order" => $this->urlIfGranted(Role::STOCK,
                'Core_Audit_report', [
                    'module' => 'Accounting',
                    'auditName' => 'WipByWorkOrder'
                ]),
            "Unbalanced stock moves" => $this->urlIfGranted(Role::STOCK, 'Core_Audit_report', [
                'module' => 'Accounting',
                'auditName' => 'MismatchedStockMoves',
            ]),
            "Actual vs standard cost of finished goods" => $this->urlIfGranted(Role::STOCK, 'Core_Audit_report', [
                'module' => 'Accounting',
                'auditName' => 'BuildCostVsStdCost',
            ]),
            "Unused parts" => $this->generateUrl('Core_Audit_report', [
                'module' => 'Stock',
                'auditName' => 'UnusedParts'
            ]),
            "Stock item list" => $this->generateUrl('stock_item_list', [
                'discontinued' => 'no',
            ]),
            "Stock bins and reels" => $this->generateUrl('stock_bin_list'),
            "Downloadable product list" => $this->generateUrl('Core_Audit_report', [
                'module' => 'Stock',
                'auditName' => 'ProductList',
            ]),
            "Discontinued items" => $this->generateUrl('stock_item_list', [
                'discontinued' => 'yes',
            ]),
            "Standard cost audit" => $this->urlIfGranted(Role::STOCK, 'Stock_StandardCost_audit'),
            "BIS self-classification report" => $this->generateUrl(
                'Core_Audit_report', [
                'module' => 'Stock',
                'auditName' => 'BisReport'
            ]),
            "Outstanding stock counts" => $this->urlIfGranted(Role::STOCK, 'stock_count_list', [
                'counted' => 'no',
            ]),
            "Approve stock counts" => $this->urlIfGranted(Role::STOCK, 'stock_count_list', [
                'counted' => 'yes',
                'approved' => 'no',
            ]),
            "Most recent stock moves by bin" => $this->generateUrl('Core_Audit_report', [
                'module' => 'Stock',
                'auditName' => 'BinLastMove',
            ]),
            "Item velocity" => $this->generateUrl('Core_Audit_report', [
                'module' => 'Stock',
                'auditName' => 'ItemVelocity',
            ]),
            "Publications" => $this->generateUrl('stock_publication_list'),
        ];

        return array_filter($reportLinks);
    }

    public function getMaintenanceLinks()
    {
        $links = [];
        if ($this->isGranted(Role::STOCK)) {
            $links["Add Inventory Item"] = $this->generateUrl('stock_item_create');
            $links["Modify Inventory Item"] = $this->generateUrl('stock_item_select');
            $links["Maintain Reorder Levels"] = $this->generateUrl('Stock_OrderPoint_edit');
            $links["Modify Standard Costs"] = $this->generateUrl('stock_item_select');
            $links["Batch stock update"] = $this->generateUrl('Stock_BatchUpdate');
            $links["Shipment prohibitions"] = $this->generateUrl('shipment_prohibition_list');
            $links['Bin style list'] = $this->generateUrl('easyadmin', [
                'entity' => 'BinStyle',
            ]);
            $links['Shelving racks'] = $this->generateUrl('rack_list');
        }

        return $links;
    }
}

class ManufacturingTab extends AbstractTab
{
    const KEY = 'manuf';

    public function getTabName()
    {
        return 'Manufacturing';
    }

    public function isVisibleToUser(User $user)
    {
        return $this->isGranted(Role::MANUFACTURING);
    }

    public function getTransactionLinks()
    {
        $transactionsLinks = [
            "Work orders" => $this->generateUrl('workorder_list', [
                'closed' => 'no',
                'parents' => 'no',
                'rework' => 'no',
                'sellable' => 'yes',
            ]),
            "Rework orders" => $this->generateUrl('workorder_list', [
                'closed' => 'no',
                'rework' => 'yes',
            ]),
            "Create panelized orders" => $this->generateUrl('panelization_create'),
        ];
        return $transactionsLinks;
    }

    public function getReportLinks()
    {
        $links = [];
        $links["Where used inquiry"] = $this->generateUrl('item_version_list');
        $links["Eagle components listing"] = $this->generateUrl(
            'Core_Audit_report', [
            'module' => 'Stock',
            'auditName' => 'EagleParts',
        ]);
        $links["Possible duplicate parts"] = $this->generateUrl(
            'Core_Audit_report', [
            'module' => 'Stock',
            'auditName' => 'DuplicateParts',
        ]);
        $links["Production dashboard"] = $this->generateUrl('manufacturing_dashboard');
        $links["Production log"] = $this->generateUrl('production_log');

        $links['Geppetto component list'] = $this->generateUrl('Core_Audit_report', [
            'module' => 'Manufacturing',
            'auditName' => 'GeppettoItemList',
        ]);
        $links['Geppetto component velocity'] = $this->generateUrl('Core_Audit_report', [
            'module' => 'Manufacturing',
            'auditName' => 'GeppettoItemVelocity',
        ]);
        $links['Geppetto Module BOM Costs'] = $this->generateUrl('Core_Audit_report', [
            'module' => 'Manufacturing',
            'auditName' => 'ModuleBomCost',
        ]);
        $links['Geppetto Module Primary Manufacturers'] = $this->generateUrl('Core_Audit_report', [
            'module' => 'Manufacturing',
            'auditName' => 'ModulePrimaryManufacturers',
        ]);

        return $links;
    }

    public function getMaintenanceLinks()
    {
        $links = [];
        $links["Customizations"] = $this->generateUrl('customization_list', [
            'active' => 'yes',
        ]);
        $links["Substitutions"] = $this->generateUrl('substitution_list');
        $links["Scrap counts"] = $this->generateUrl('scrapcount_edit');
        $links['Allocation Configurations'] = $this->generateUrl('allocation_sources_prioritization');

        return $links;
    }

}

class GeneralLedgerTab extends AbstractTab
{
    const KEY = 'GL';

    private $dbm;

    public function __construct(RouterInterface $router,
                                AuthorizationCheckerInterface $auth,
                                DbManager $dbm)
    {
        parent::__construct($router, $auth);
        $this->dbm = $dbm;
    }

    public function getTabName()
    {
        return 'General Ledger';
    }

    public function isVisibleToUser(User $user)
    {
        return $this->isGranted(Role::ACCOUNTING);
    }

    public function getTransactionLinks()
    {
        $sweepCreditCardTransactions = $this->generateUrl('Payment_CardTransaction_sweep', [
            'date' => date('Y-m-d'),
        ]);

        $links = [];
        $links["Sweep Credit Card Transactions"] = $sweepCreditCardTransactions;
        $links["Enter a Bank Transfer"] = $this->generateUrl('banktransfer_create');
        $links["Load Bank Statements"] = $this->generateUrl(
            'Accounting_BankStatement_load');
        $links["Reconcile Bank Statements"] = $this->generateUrl(
            'Accounting_BankStatement_match');
        $links["Manually enter a GL transaction"] = $this->generateUrl('transaction_create');
        return $links;
    }

    public function getReportLinks()
    {
        $period = $this->getDefaultStartingPeriod();
        $lastYear = (int) date('Y') - 1;
        $firstDay = new DateTime("first day of January $lastYear");
        $lastDay = new DateTime("last day of December $lastYear");

        $reportLinks = [
            "Account balances by period" => $this->generateUrl('account_balance_list', [
                'fromPeriod' => $period->getId(),
            ]),
            "Recent GL transactions" => $this->generateUrl('accounting_transaction_list', [
                'startDate' => date('Y-m-d', strtotime('-1 week')),
            ]),
            "Uploaded Bank Statements" => $this->generateUrl('Accounting_BankStatement_list'),
            "Profit and loss statement" => $this->generateUrl('Accounting_ProfitAndLoss'),
            "Balance sheet" => $this->generateUrl('Accounting_BalanceSheet'),
            "Sales tax report" => $this->generateUrl('Tax_SalesTaxReport'),
            "Tax audit" => $this->generateUrl('Core_Audit_report', [
                'module' => 'Accounting',
                'auditName' => 'TaxAudit',
                'yearBegin' => $firstDay->format('Y-m-d'),
                'yearEnd' => $lastDay->format('Y-m-d'),
            ]),
            "Unbalanced GL transactions" => $this->generateUrl('Core_Audit_report', [
                'module' => 'Accounting',
                'auditName' => 'GLEntryBalance',
                'threshold' => 0.05,
            ]),
            "Outstanding bank transactions" => $this->generateUrl('banktransaction_list', [
                'cleared' => 'no',
                'bankAccount' => GLAccount::REGULAR_CHECKING_ACCOUNT,
                'startDate' => date('Y-m-d', strtotime('-6 weeks')),
            ])
        ];

        return $reportLinks;
    }

    private function getDefaultStartingPeriod()
    {
        $year = (int) date('Y');
        $startDate = new DateTime();
        $startDate->setDate($year, 1, 1);
        /** @var $periodRepo PeriodRepository */
        $periodRepo = $this->dbm->getRepository(Period::class);
        return $periodRepo->findForDate($startDate);
    }

    public function getMaintenanceLinks()
    {
        $links = [];
        $links["Bank statement patterns"] = $this->generateUrl('bank_statement_pattern_list');
        if ($this->isGranted(Role::ADMIN)) {
            $links['Repost GL account balances'] =
                $this->generateUrl('Accounting_AccountBalance_repost');
        }
        $links['Edit payment terms'] = $this->generateUrl('payment_terms_edit');

        return $links;
    }

}

class SetupTab extends AbstractTab
{
    const KEY = 'system';

    private $logUrl;

    public function __construct(RouterInterface $router,
                                AuthorizationCheckerInterface $auth,
                                $sentryDsn)
    {
        parent::__construct($router, $auth);
        $this->logUrl = $this->getLogUrl($sentryDsn);
    }

    private function getLogUrl($sentryDsn)
    {
        $urlParts = parse_url($sentryDsn);
        return strtr('scheme://host/', $urlParts);
    }

    public function getTabName()
    {
        return 'Setup';
    }

    public function isVisibleToUser(User $user)
    {
        return $this->isGranted(Role::ADMIN);
    }

    public function getTransactionLinks()
    {
        $links = [];
        $links["Company Preferences"] = $this->generateUrl('company_view');
        $links["User Accounts"] = $this->generateUrl('user_list', [
            'active' => 'yes',
        ]);
        $links["Test email"] = $this->generateUrl('Email_Test');
        return $links;
    }

    public function getReportLinks()
    {
        $links = [];
        $links["Shippers"] = $this->generateUrl('shipper_list');
        $links["Harmonization codes"] = $this->generateUrl('hscode_list');
        $links["Error log (Sentry)"] = $this->logUrl;
        $links["Email log"] = $this->generateUrl('email_log');
        $links["Automation log"] = $this->generateUrl('automation_log');
        $links["Print jobs"] = $this->generateUrl('print_job_list');
        $links["PHP configuration"] = $this->generateUrl('status_phpinfo');
        $links["Command queue"] = $this->generateUrl('jms_jobs_overview');

        return $links;
    }

    public function getMaintenanceLinks()
    {
        $links = [];
        $links["Inventory Categories Maintenance"] = $this->generateUrl('stock_category_list');
        $links["Inventory Locations Maintenance"] = $this->generateUrl('stock_facility_list');
        $links["Content management"] = $this->generateUrl('cms_entry_list');
        $links["Tax authorities"] = $this->generateUrl('easyadmin', [
            'entity' => 'TaxAuthority',
        ]);
        $links['Sales tax regimes'] = $this->generateUrl('tax_regime_list');
        $links["Accounting periods"] = $this->generateUrl('period_list');
        $links["Payment methods"] = $this->generateUrl('payment_method_list');
        $links["Document and form filing"] = $this->generateUrl('filing_document_list');
        $links["Printers"] = $this->generateUrl('printers_edit');
        $links['Allocation Configurations'] = $this->generateUrl('allocation_sources_prioritization');
        $links["Admin"] = $this->generateUrl('easyadmin');

        return $links;
    }
}
