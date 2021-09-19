<?php

namespace Rialto\Sales\Invoice\Label;

use Rialto\Printing\Printer\PrintServer;
use Rialto\Sales\Invoice\SalesInvoice;
use Rialto\Sales\Invoice\SalesInvoiceItem;
use Rialto\Shipping\Label\Ecia\UnitPackLabel;


/**
 * Prints ECIA-compliant labels for a sales invoice.
 */
class EciaLabelManager
{
    const PRINTER_ID = 'zebra_label';

    /** @var PrintServer */
    private $printers;

    public function __construct(PrintServer $printers)
    {
        $this->printers = $printers;
    }

    public function printLabels(SalesInvoice $invoice)
    {
        foreach ($invoice->getLineItems() as $item) {
            if ($item->getQtyToShip() > 0) {
                $this->printLabelsForItem($item);
            }
        }
    }

    private function printLabelsForItem(SalesInvoiceItem $item)
    {
        if ($item->isAssembly()) {
            $label = UnitPackLabel::fromOrderItem($item);
            $label->setCustomerPart($item->getCustomerPartNo() ?: $item->getSku());
            $this->printLabel($label);
        } else {
            foreach ($item->getAllocations() as $alloc) {
                $label = UnitPackLabel::fromAllocation($alloc);
                $label->setCustomerPart($item->getCustomerPartNo() ?: $alloc->getSource()->getSku());
                $this->printLabel($label);
            }
        }
    }

    private function printLabel(UnitPackLabel $label)
    {
        $epl = $label->generateEpl();
        $this->printers->printString(self::PRINTER_ID, $epl);
    }
}
