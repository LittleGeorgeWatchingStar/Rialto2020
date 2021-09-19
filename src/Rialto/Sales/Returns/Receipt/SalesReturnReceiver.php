<?php

namespace Rialto\Sales\Returns\Receipt;

use Rialto\Accounting\Debtor\DebtorCredit;
use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Accounting\Debtor\DebtorTransactionFactory;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Allocation\Source\StockSource;
use Rialto\Company\Company;
use Rialto\Stock\Bin\StockBin;
use Rialto\Stock\Bin\StockCreationEvent;
use Rialto\Stock\StockEvents;

/**
 * Processes sales returns that have just be received back from the customer.
 */
class SalesReturnReceiver extends DebtorTransactionFactory
{
    /** @return DebtorTransaction */
    public function receive(SalesReturnReceipt $receipt)
    {
        $glTrans = new Transaction($this->getCreditNoteSystemType());
        $glTrans->setMemo('Receive '. $receipt->getRmaNumber());

        foreach ( $receipt->getLineItems() as $item ) {
            if ( $item->getQuantity() <= 0 ) continue;

            $bin = $item->receive($glTrans);
            $this->dbm->persist($bin);
            $this->dbm->flush(); // bins need IDs before allocs can be updated.
            $item->addStockEntries($glTrans);
            $item->addDebtorEntries($glTrans);

            $item->updateInstructions($bin);
            $this->notifyOfNewStock($item, $bin);
            $receipt->mergeInstructions($item->getInstructions());
        }

        $this->addDebtorEntries($receipt, $glTrans);
        $this->dbm->persist($glTrans);

        $debtorTrans = new DebtorCredit($glTrans, $receipt->getCustomer());
        $debtorTrans->setSubtotalAmount( -$receipt->getSubtotalPrice() )
            ->setShippingAmount( -$receipt->getShippingPrice() )
            ->setTaxAmount( -$receipt->getTaxAmount() )
            ->setReference( 'Inv-' . $receipt->getOriginalInvoiceNumber() );

        $replacementOrder = $receipt->getReplacementOrder();
        if ( $replacementOrder ) {
            $debtorTrans->allocateToOrder($replacementOrder);
        }
        $this->dbm->persist($debtorTrans);

        if ( $replacementOrder && $replacementOrder->isQuotation() ) {
            $replacementOrder->convertToOrder();
        }

        return $debtorTrans;
    }

    /** @return SystemType */
    private function getCreditNoteSystemType()
    {
        return $this->dbm->need(SystemType::class, SystemType::CREDIT_NOTE);
    }

    private function notifyOfNewStock(StockSource $receipt, StockBin $bin)
    {
        $event = new StockCreationEvent($receipt, $bin);
        $this->dispatcher->dispatch(StockEvents::STOCK_CREATION, $event);
    }

    private function addDebtorEntries(SalesReturnReceipt $receipt, Transaction $glTrans)
    {
        $total = $receipt->getTotalPrice();
        if ( 0 == $total ) { return; }

        $this->addOffsetEntries($glTrans, $total, $receipt->getReplacementOrder());

        $shippingPrice = $receipt->getShippingPrice();
        if ( 0 != $shippingPrice ) {
            $company = Company::findDefault($this->dbm);
            $shippingAcct = $company->getShippingAccount();
            $glTrans->addEntry($shippingAcct, $shippingPrice);
        }

        $taxAmount = $receipt->getTaxAmount();
        if ( 0 != $taxAmount ) {
            $taxAcct = $receipt->getTaxAccount();
            $glTrans->addEntry($taxAcct, $taxAmount);
        }
    }

}
