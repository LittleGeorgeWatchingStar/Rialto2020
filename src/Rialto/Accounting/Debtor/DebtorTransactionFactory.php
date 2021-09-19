<?php

namespace Rialto\Accounting\Debtor;

use Rialto\Accounting\Card\CardTransaction;
use Rialto\Accounting\Debtor\Credit\CustomerCredit;
use Rialto\Accounting\Debtor\Credit\HasBankingFee;
use Rialto\Accounting\Ledger\Account\GLAccount;
use Rialto\Accounting\Ledger\Entry\GLEntry;
use Rialto\Accounting\Transaction\SystemType;
use Rialto\Accounting\Transaction\Transaction;
use Rialto\Company\Company;
use Rialto\Database\Orm\DbManager;
use Rialto\Sales\Order\SalesOrder;
use Rialto\Sales\SalesEvents;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;


/**
 * Creates the accounting records for a customer transaction.
 */
class DebtorTransactionFactory
{
    /** @var DbManager */
    protected $dbm;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    public function __construct(
        DbManager $dbm,
        EventDispatcherInterface $dispatcher)
    {
        $this->dbm = $dbm;
        $this->dispatcher = $dispatcher;
    }

    public function createCredit(CustomerCredit $credit): DebtorTransaction
    {
        if ( $credit instanceof HasBankingFee ) {
            $this->createCreditNoteForBankingFee($credit);
        }
        $glTrans = new Transaction($credit->getSystemType($this->dbm));
        $glTrans->setDate($credit->getDate());
        $glTrans->setMemo($credit->getMemo());

        $creditAmount = $credit->getAmount();
        $salesOrder = $credit->getSalesOrder();
        $glTrans->addEntry($credit->getToAccount(), $creditAmount);
        $this->addOffsetEntries($glTrans, $creditAmount, $salesOrder);
        $this->dbm->persist($glTrans);

        $debtorTrans = new DebtorCredit($glTrans, $credit->getCustomer());
        $debtorTrans->setSubtotalAmount(-$creditAmount);
        if ( $salesOrder ) {
            $debtorTrans->allocateToOrder($salesOrder);
        }
        $this->dbm->persist($debtorTrans);

        $credit->createAdditionalTransactions($glTrans, $this->dbm);

        $this->notifyOfCredit($credit);
        return $debtorTrans;
    }

    private function createCreditNoteForBankingFee(HasBankingFee $credit)
    {
        if ( $credit->getFeeAmount() <= 0 ) return;
        $creditNote = $credit->createCreditNoteForBankingFee();
        $this->createCredit($creditNote);
    }

    protected function addOffsetEntries(Transaction $glTrans, $creditAmount, SalesOrder $salesOrder = null)
    {
        $amtOwed = $salesOrder ? $salesOrder->getAmountOwedByCustomer() : 0;

        $creditAmount = $this->round($creditAmount);
        $amtFromReceivable = $this->round(min($creditAmount, $amtOwed));
        $amtFromPrepaid = $this->round($creditAmount - $amtFromReceivable);
        assertion($amtFromPrepaid >= 0);
        assertion($this->areEqual($amtFromReceivable + $amtFromPrepaid, $creditAmount)); // prevent rounding errors

        if ( $amtFromReceivable > 0 ) {
            $company = Company::findDefault($this->dbm);
            $acctsRec = $company->getDebtorAccount();
            $glTrans->addEntry($acctsRec, -$amtFromReceivable);
        }
        if ( $amtFromPrepaid > 0 ) {
            $prepaidRev = $this->getPrepaidAccount();
            $glTrans->addEntry($prepaidRev, -$amtFromPrepaid);
        }
    }

    /** Round monetary amounts. */
    private function round($amount)
    {
        return GLEntry::round($amount);
    }

    /** Check monetary amounts for equality. */
    private function areEqual($a, $b)
    {
        return GLEntry::areEqual($a, $b);
    }

    private function getPrepaidAccount(): GLAccount
    {
        return GLAccount::fetchPrepaidRevenue($this->dbm);
    }

    private function notifyOfCredit(CustomerCredit $credit)
    {
        $event = new CustomerCreditEvent($credit);
        $this->dispatcher->dispatch(SalesEvents::CUSTOMER_CREDIT, $event);
    }

    /**
     * Creates the receipt transaction for the given credit card payment.
     *
     * @return DebtorCredit
     */
    public function createCardReceipt(CardTransaction $payment)
    {
        assertion($payment->getAmountCaptured() > 0);
        $glTrans = $this->createGLTransaction($payment);
        $payment->setAccountingTransaction($glTrans);
        return $this->createDebtorTransaction($glTrans, $payment);
    }

    /** @return Transaction */
    private function createGLTransaction(CardTransaction $cardTrans)
    {
        $customer = $cardTrans->getCustomer();
        assertion(null != $customer);
        $order = $cardTrans->getSalesOrder(); // might be null

        $sysType = SystemType::fetchReceipt($this->dbm);
        $glTrans = new Transaction($sysType);
        $glTrans->setDate($cardTrans->getDateCaptured());
        $glTrans->setMemo(sprintf('%s payment for %s',
            $cardTrans->getCardName(),
            $order ?: $customer));
        $amountCharged = $cardTrans->getAmountCaptured();
        $card = $cardTrans->getCreditCard();

        /* Insert the order price into the general ledger. */
        $entry = $glTrans->addEntry(
            $card->getDepositAccount(),
            $amountCharged);
        $entry->setSalesOrder($order);

        $this->addOffsetEntries($glTrans, $amountCharged, $order);

        /* Insert the transaction fees into the general ledger. */
        $fees = $card->getTotalFees($amountCharged);
        $feeNarrative = sprintf('%s processing fee - %s',
            $card->getDepositAccountName(),
            $card->getName());
        $entry = $glTrans->addEntry(
            $card->getFeeAccount(),
            -$fees,
            $feeNarrative);
        $entry->setSalesOrder($order);

        $glTrans->addEntry(
            $this->getBankChargesAccount(),
            $fees,
            $feeNarrative);

        $this->dbm->persist($glTrans);
        return $glTrans;
    }

    /** @return DebtorCredit */
    private function createDebtorTransaction(Transaction $glTrans, CardTransaction $payment)
    {
        $debtorTrans = new DebtorCredit($glTrans, $payment->getCustomer());
        $debtorTrans->setSubtotalAmount(-$payment->getAmountCaptured());
        $order = $payment->getSalesOrder();
        if ($order) {
            $debtorTrans->allocateToOrder($order);
        }
        $this->dbm->persist($debtorTrans);

        return $debtorTrans;
    }

    /** @return GLAccount */
    private function getBankChargesAccount()
    {
        return GLAccount::fetch(GLAccount::BANK_CHARGES, $this->dbm);
    }

}
