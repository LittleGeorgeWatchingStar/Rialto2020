<?php

namespace Rialto\Purchasing\Invoice\Cli;

use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Database\Orm\DbManager;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Purchasing\Order\Orm\PurchaseOrderRepository;
use Rialto\Purchasing\Order\PurchaseOrder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Find purchase orders with uninvoiced inventory.
 */
class FindUninvoicedOrders extends Command
{
    const NAME = 'purchasing:find-uninvoiced-orders';

    /** @var DbManager */
    private $dbm;

    /** @var  PurchaseOrderRepository */
    private $orders;

    /** @var OutputInterface */
    private $output;

    public function __construct(DbManager $dbm)
    {
        parent::__construct();
        $this->dbm = $dbm;
        $this->orders = $dbm->getRepository(PurchaseOrder::class);
    }

    protected function configure()
    {
        $this
            ->setName(self::NAME)
            ->setAliases(['rialto:find-uninvoiced-POs'])
            ->setDescription('Find purchase orders with uninvoiced inventory')
            ->addArgument('year', InputArgument::REQUIRED, 'Show POs for this year');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $poList = $this->findPOs($input->getArgument('year'));
        $totalBalance = 0;
        foreach ( $poList as $po ) {
            $totalBalance += $this->checkPO($po);
        }
        $this->output->writeln(sprintf(
            "\nTotal balance is %12.2f",
            $totalBalance
        ));
    }

    private function findPOs($year)
    {
        $startDate = "$year-01-01";
        $endDate = "$year-12-31 23:59:59";
        $qb = $this->orders->createQueryBuilder('po')
            ->where('po.datePrinted >= :startDate')
            ->setParameter('startDate', $startDate)
            ->andWhere('po.datePrinted <= :endDate')
            ->setParameter('endDate', $endDate);
        return $qb->getQuery()->getResult();
    }

    private function checkPO(PurchaseOrder $po)
    {
        $receiptTotal = $this->getReceiptTotal($po);
        $invoiceTotal = $this->getInvoiceTotal($po);
        $balance = $receiptTotal + $invoiceTotal;
        if ( $balance != 0 ) {
            $this->output->writeln(sprintf(
                '<error>PO %s balance: %10.2f</error>',
                $po->getId(),
                $balance
            ));
        }
        return $balance;
    }

    private function getReceiptTotal(PurchaseOrder $po)
    {
        $grns = $po->getReceipts();
        $total = 0;
        foreach ( $grns as $grn ) {
            $entries = $grn->getGLEntries();
            $total += $this->getUninvoicedInventoryTotal($entries);
        }
        return $total;
    }

    private function getUninvoicedInventoryTotal(array $glEntries)
    {
        $total = 0;
        foreach ( $glEntries as $entry ) {
            if ( $entry->getAccountCode() == GLAccount::UNINVOICED_INVENTORY ) {
                $total += $entry->getAmount();
            }
        }
        return $total;
    }

    private function getInvoiceTotal(PurchaseOrder $po)
    {
        $total = 0;
        $invoices = $this->getInvoices($po);
        foreach ( $invoices as $invoice ) {
            $transList = $this->getSupplierTransactions($invoice);
            foreach ( $transList as $suppTrans ) {
                $entries = $suppTrans->getGLEntries();
                $total += $this->getUninvoicedInventoryTotal($entries);
            }
        }
        return $total;
    }

    /**
     * @return SupplierInvoice[]
     */
    private function getInvoices(PurchaseOrder $po)
    {
        $repo = $this->dbm->getRepository(SupplierInvoice::class);
        return $repo->findBy([
            'purchaseOrder' => $po->getId(),
        ]);
    }

    /**
     * @return SupplierTransaction[]
     */
    private function getSupplierTransactions(SupplierInvoice $invoice)
    {
        $repo = $this->dbm->getRepository(SupplierTransaction::class);
        return $repo->findBy([
            'supplier' => $invoice->getSupplier()->getId(),
            'reference' => $invoice->getSupplierReference(),
        ]);
    }
}
