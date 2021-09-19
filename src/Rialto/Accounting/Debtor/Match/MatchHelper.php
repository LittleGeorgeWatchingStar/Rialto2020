<?php

namespace Rialto\Accounting\Debtor\Match;

use Rialto\Accounting\Debtor\DebtorTransaction;
use Rialto\Sales\Customer\Customer;
use Rialto\Sales\Order\SalesOrder;

class MatchHelper
{
    private $customer;
    private $order = null;
    private $matches = [];
    private $unmatched = [];

    public function __construct(Customer $cust, SalesOrder $order = null)
    {
        $this->customer = $cust;
        $this->order = $order;
    }

    public function addTransaction(DebtorTransaction $trans)
    {
        foreach ($this->unmatched as $id => $other) {
            if ($this->isMatch($trans, $other)) {
                $match = $this->createMatch($trans, $other);
                $key = $match->getIndexKey();
                $this->matches[$key] = $match;
                unset($this->unmatched[$id]);
                return;
            }
        }

        $this->unmatched[$trans->getId()] = $trans;
    }

    private function isMatch(DebtorTransaction $first, DebtorTransaction $second)
    {
        return 0 == $this->round(
                $first->getAmountUnallocated() +
                $second->getAmountUnallocated()
            );
    }

    private function round($amount)
    {
        return round($amount, DebtorTransaction::MONEY_PRECISION);
    }

    private function createMatch(
        DebtorTransaction $first,
        DebtorTransaction $second = null)
    {
        $match = new TransactionMatch($this->customer, $this->order);
        $match->addTransaction($first);
        if ($second) $match->addTransaction($second);
        return $match;
    }

    /**
     * @return TransactionMatch[]
     */
    public function getMatches()
    {
        $matches = $this->matches;

        foreach ($this->unmatched as $trans) {
            $match = $this->createMatch($trans);
            $key = $match->getIndexKey();
            $matches[$key] = $match;
        }
        return $matches;
    }
}
