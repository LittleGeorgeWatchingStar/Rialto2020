<?php

namespace Rialto\Accounting\Bank\Statement\Match;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Rialto\Accounting\Debtor\Credit\WireReceipt;
use Rialto\Sales\Order\Orm\SalesOrderRepository;
use Rialto\Sales\Order\SalesOrder;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * This strategy finds an open sales order or quotation for which the
 * bank statement item might be a prepayment.
 */
class CustomerPrepaymentStrategy extends CustomerPaymentStrategy
{
    /** @var SalesOrder[] */
    private $matchingOrders = [];

    /** @var SalesOrder[] */
    private $acceptedOrders;

    /**
     * @var float
     * @Assert\Type(type="numeric", message="Transfer fee must be a valid number.")
     * @Assert\Range(min=0, minMessage="Transfer fee cannot be negative.")
     */
    private $transferFee = null;
    private $sendEmail = [];

    protected function initializeCollections()
    {
        parent::initializeCollections();
        $this->acceptedOrders = new ArrayCollection();
    }

    public function loadMatchingRecords()
    {
        if (! $this->loadMatchingBankTransactions() ) {
            $this->loadMatchingSalesOrders();
        }
    }

    private function loadMatchingSalesOrders()
    {
        /** @var SalesOrderRepository $repo */
        $repo = $this->dbm->getRepository(SalesOrder::class);
        $statement = $this->getStatement();
        $this->matchingOrders = $repo->findMatchingOrdersForBankStatement($statement);
    }

    public function hasMatchingRecords(): bool
    {
        return parent::hasMatchingRecords() ||
            ( count($this->matchingOrders) > 0);
    }

    public function getMatchingOrders()
    {
        return $this->matchingOrders;
    }

    public function getAcceptedOrders()
    {
        return $this->acceptedOrders;
    }

    public function setAcceptedOrders(Collection $acceptedOrders)
    {
        $this->acceptedOrders = $acceptedOrders;
    }

    public function getTransferFee()
    {
        return $this->transferFee;
    }

    public function setTransferFee($transferFee)
    {
        $this->transferFee = $transferFee;
    }

    public function getSendEmail()
    {
        return $this->sendEmail;
    }

    public function setSendEmail(array $sendEmail)
    {
        $this->sendEmail = $sendEmail;
    }

    public function save()
    {
        if ( count($this->acceptedOrders) > 0 ) {
            $this->recordCustomerReceipts();
        }
        $this->linkBankTransactions();
    }

    private function recordCustomerReceipts()
    {
        $statementAmtRemaining = $this->getTotalOutstanding();
        foreach ( $this->acceptedOrders as $salesOrder ) {
            if ( $statementAmtRemaining <= 0 ) break;

            $amtUnpaid = $salesOrder->getTotalPrice() -
                $salesOrder->getTotalAmountPaid();
            if ( $amtUnpaid <= 0 ) continue;

            $amtToCredit = min($statementAmtRemaining, $amtUnpaid);
            $this->recordCustomerReceipt($salesOrder, $amtToCredit);
            $statementAmtRemaining -= $amtToCredit;
        }
    }

    private function recordCustomerReceipt(SalesOrder $salesOrder, $amount)
    {
        $statement = $this->getStatement();
        $wireReceipt = new WireReceipt($salesOrder->getCustomer());
        $wireReceipt->setBankAccount($this->bankAccountRepository->getDefaultChecking());
        $wireReceipt->setSalesOrder($salesOrder);
        $wireReceipt->setAmount($amount);
        $wireReceipt->setTransactionId($statement->getTransactionId());
        $wireReceipt->setDate($statement->getDate());
        $wireReceipt->setFeeAmount($this->transferFee);

        $emailRequested = in_array($salesOrder->getId(), $this->sendEmail);
        $wireReceipt->setSendEmail($emailRequested);
        $this->factory->createCredit($wireReceipt);

        $this->bankTransactions[] = $wireReceipt->getBankTransaction();
        $this->transferFee = 0;
    }
}
