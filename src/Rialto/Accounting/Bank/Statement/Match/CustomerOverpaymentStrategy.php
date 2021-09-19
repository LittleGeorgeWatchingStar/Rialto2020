<?php

namespace Rialto\Accounting\Bank\Statement\Match;

use Rialto\Accounting\Debtor\Credit\WireReceipt;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Customer\Orm\CustomerRepository;

/**
 * This strategy creates an additional customer receipt to record
 * a customer overpayment. The customer can then be credited or
 * reimbursed at a later time.
 */
class CustomerOverpaymentStrategy extends CustomerPaymentStrategy
{
    /** @var Customer[] */
    private $matchingCustomers = [];

    /** @var Customer */
    private $selectedCustomer = null;

    public function loadMatchingRecords()
    {
        if (! $this->loadMatchingBankTransactions() ) {
            $this->loadMatchingCustomers();
        }
    }

    private function loadMatchingCustomers()
    {
        $repo = $this->dbm->getRepository(Customer::class);
        /* @var $repo CustomerRepository */
        $this->matchingCustomers = $repo->findOverpaidCustomersForBankStatement($this->getStatement());
    }

    public function hasMatchingRecords(): bool
    {
        return parent::hasMatchingRecords() ||
            ( count($this->matchingCustomers) > 0 );
    }

    public function getMatchingCustomers()
    {
        return $this->matchingCustomers;
    }

    public function getSelectedCustomer()
    {
        return $this->selectedCustomer;
    }

    public function setSelectedCustomer(Customer $customer = null)
    {
        $this->selectedCustomer = $customer;
    }

    public function save()
    {
        if ( $this->selectedCustomer ) {
            $this->recordCustomerOverpayment();
        }
        $this->linkBankTransactions();
    }

    private function recordCustomerOverpayment()
    {
        $statement = $this->getStatement();
        $wireReceipt = new WireReceipt($this->selectedCustomer);
        $wireReceipt->setBankAccount($this->bankAccountRepository->getDefaultChecking());
        $wireReceipt->setAmount($statement->getAmountOutstanding());
        $wireReceipt->setTransactionId($statement->getTransactionId());
        $wireReceipt->setDate($statement->getDate());
        $wireReceipt->setMemo(sprintf('Overpayment from %s',
            $this->selectedCustomer
        ));
        $debtorTrans = $this->factory->createCredit($wireReceipt);
        $this->dbm->persist($debtorTrans);

        $this->bankTransactions[] = $wireReceipt->getBankTransaction();
    }
}
