<?php

namespace Rialto\Ups\Invoice;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\UnexpectedResultException;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Purchasing\Invoice\Orm\SupplierInvoiceRepository;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Purchasing\Invoice\SupplierInvoiceItem;
use Rialto\Purchasing\Supplier\Orm\SupplierRepository;
use Rialto\Purchasing\Supplier\Supplier;

abstract class InvoiceParserAbstract implements InvoiceParser
{
    /** @var SupplierInvoiceRepository */
    private $invoiceRepo;

    /** @var SupplierRepository */
    private $supplierRepo;

    /** @var ObjectManager */
    private $om;

    /**
     * The supplier ID of UPS Supply Chain Solutions.
     *
     * This is a dirty little hack to account for the fact that we have the
     * same account number for UPS and UPS Supply Chain Solutions, which are
     * distinct suppliers.
     *
     * @var int|string
     */
    private $supplyChainId;

    public function __construct(ObjectManager $om, $supplyChainId)
    {
        $this->invoiceRepo = $om->getRepository(SupplierInvoice::class);
        $this->supplierRepo = $om->getRepository(Supplier::class);
        $this->om = $om;
        $this->supplyChainId = $supplyChainId;
    }

    protected static function normalize($value)
    {
        return strtoupper(trim($value));
    }

    /** @return Supplier */
    protected function findSupplier($accountNo, $invoiceNo)
    {
        if (!is_substring($accountNo, $invoiceNo)) {
            /* I refer you to the dirty little hack mentioned above. */
            return $this->supplierRepo->find($this->supplyChainId);
        }
        // The account no in the invoice usually has leading zeroes.
        $accountNo = ltrim($accountNo, '0');
        try {
            return $this->supplierRepo->findByAccountNumber($accountNo);
        } catch (UnexpectedResultException $ex) {
            throw new InvoiceParseException($accountNo, $ex);
        }
    }

    protected function findExisting(Supplier $supplier, $invoiceNo)
    {
        return $this->invoiceRepo->findBySupplierReference($supplier, $invoiceNo);
    }

    /** @return SupplierInvoice */
    protected function createInvoice(Supplier $supplier, $invoiceNo, $amount, $invDate, $dueDate)
    {
        $invoice = new SupplierInvoice($supplier);
        $invoice->setDate($invDate);
        $invoice->setSupplierReference($invoiceNo);
        $invoice->setTotalCost($amount);

        $item = new SupplierInvoiceItem();
        $item->setLineNumber(1);
        $item->setQtyOrdered(1);
        $item->setQtyInvoiced(1);
        $item->setUnitCost($amount);
        $item->setGLAccount(GLAccount::fetchShippingExpenses($this->om));
        $item->setDescription('SHIPPING');
        $invoice->addItem($item);

        return $invoice;
    }
}
