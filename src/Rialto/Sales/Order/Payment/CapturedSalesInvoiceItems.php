<?php


namespace Rialto\Sales\Order\Payment;


use Doctrine\Common\Collections\ArrayCollection;
use Rialto\Accounting\Card\CapturableInvoice;
use Rialto\Accounting\Debtor\DebtorInvoice;
use Rialto\Sales\Invoice\SalesInvoiceItem;
use Rialto\Sales\Order\SalesOrder;

class CapturedSalesInvoiceItems
{
    /** @var SalesInvoiceItem[]&ArrayCollection */
    private $lineItems;

    /**
     * @param SalesOrder[] $orders
     * @return CapturedSalesInvoiceItems
     */
    public static function fromSalesOrders(array $orders): self
    {
        $capturedItems = array_map(function (SalesOrder $order) {
            return self::fromSalesOrder($order);
        }, $orders);

        $lineItems = array_merge([], ...array_map(function (self $cs) {
            return $cs->getLineItems();
        }, $capturedItems));

        return new self($lineItems);
    }

    public static function fromSalesOrder(SalesOrder $order): self
    {
        $validInvoices = array_filter($order->getInvoices(),
            function (DebtorInvoice $invoice) {
                return self::validInvoice($invoice);
        });

        $lineItems = array_merge([], ...array_map(function (DebtorInvoice $invoice) {
            return $invoice->getLineItems();
        }, $validInvoices));

        return new self($lineItems);
    }

    /**
     * @param SalesInvoiceItem[] $lineItems
     */
    private function __construct(array $lineItems)
    {
        $this->lineItems = new ArrayCollection($lineItems);
    }

    private static function validInvoice(CapturableInvoice $invoice): bool
    {
        return $invoice->getAmountToCapture() <= 0;
    }

    /**
     * @return SalesInvoiceItem[]
     */
    public function getLineItems(): array
    {
        return $this->lineItems->getValues();
    }

    /**
     * @return string[]
     */
    public function getUniqueSkus(): array
    {
        $skus = $this->lineItems->map(function (SalesInvoiceItem $item) {
            return $item->getSku();
        });

        return array_values(
            array_unique($skus->getValues())
        );
    }
}