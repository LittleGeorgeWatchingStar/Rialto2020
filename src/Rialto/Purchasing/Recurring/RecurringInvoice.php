<?php

namespace Rialto\Purchasing\Recurring;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Supplier\SupplierTransaction;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Company\Company;
use Rialto\Entity\RialtoEntity;
use Rialto\Purchasing\Invoice\SupplierInvoice;
use Rialto\Purchasing\Supplier\Supplier;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * A template for a supplier invoice that recurs regularly.
 */
class RecurringInvoice implements RialtoEntity
{
    private $id;

    /**
     * @var Supplier
     * @Assert\NotNull
     */
    private $supplier;

    /**
     * @var RecurringInvoiceDetail[]
     * @Assert\Valid(traverse=true)
     * @Assert\Count(min=1, minMessage="Please add at least one line item.")
     */
    private $details;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $reference;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $dates;

    /**
     * @deprecated This amount is calculated from the details, and
     * maintained for legacy purposes only.
     */
    private $subtotalAmount = 0;

    public function __construct()
    {
        $this->details = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function getSupplier()
    {
        return $this->supplier;
    }

    public function setSupplier(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    public function getDetails()
    {
        return $this->details->toArray();
    }

    public function createDetail(GLAccount $account,
                                 float $amount,
                                 string $reference): RecurringInvoiceDetail
    {
        $detail = new RecurringInvoiceDetail();
        $detail->setAccount($account);
        $detail->setAmount($amount);
        $detail->setReference($reference);
        $this->addDetail($detail);
        return $detail;
    }

    public function addDetail(RecurringInvoiceDetail $detail)
    {
        $this->details[] = $detail;
        $detail->setInvoice($this);
        $this->subtotalAmount = $this->getSubtotalAmount();
    }

    public function removeDetail(RecurringInvoiceDetail $detail)
    {
        $this->details->removeElement($detail);
        $this->subtotalAmount = $this->getSubtotalAmount();
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function setReference($reference)
    {
        $this->reference = trim($reference);
    }

    /**
     * @return int[]
     * @Assert\All({
     *   @Assert\Range(min=1, max=28)
     * })
     */
    public function getDates()
    {
        return array_map(function ($string) {
            return (int) $string;
        }, explode(',', $this->dates));
    }

    public function setDates(array $dates)
    {
        sort($dates);
        $this->dates = join(',', $dates);
    }

    public function getSubtotalAmount()
    {
        $total = 0;
        foreach ($this->details as $detail) {
            $total += $detail->getAmount();
        }
        return $total;
    }

    public function getDeprecatedSubtotal()
    {
        return $this->subtotalAmount;
    }

    public function __toString()
    {
        return $this->supplier
            ? sprintf('%s for %s',
                $this->reference,
                $this->supplier->getName())
            : 'new recurring invoice';
    }


    public function createInvoice(DateTime $date): SupplierInvoice
    {
        $invoice = new SupplierInvoice($this->supplier);
        $invoice->setDate($date);
        $invoice->setTotalCost($this->getSubtotalAmount());
        $invoice->setSupplierReference(sprintf('%s - %s',
            $this->reference,
            $date->format('Y-m-d')));
        foreach ($this->details as $i => $template) {
            $lineNumber = $i + 1;
            $invoice->addItem($template->createInvoiceItem($lineNumber));
        }
        return $invoice;
    }

    public function createSupplierTransaction(SupplierInvoice $invoice,
                                              SystemType $sysType,
                                              Company $company): SupplierTransaction
    {
        $invoice->prepare();
        $suppTrans = $invoice->approve($sysType, $company);

        $dueDate = clone $invoice->getDate();
        $dueDate->modify("+4 days");
        $suppTrans->setDueDate($dueDate);
        $suppTrans->setRecurringInvoice($this);

        return $suppTrans;
    }

}
