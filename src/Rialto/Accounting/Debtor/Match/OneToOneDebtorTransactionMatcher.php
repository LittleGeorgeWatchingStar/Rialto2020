<?php

namespace Rialto\Accounting\Debtor\Match;

use Rialto\Accounting\Debtor\DebtorTransaction;

/**
 * @author Ian Phillips <ian@gumstix.com>
 */
class OneToOneDebtorTransactionMatcher extends DebtorTransactionMatcher
{
    /**
     * @param DebtorTransaction[] $transactions
     * @return TransactionMatch[]
     */
    public function findMatches(array $transactions)
    {
        $index = [];
        foreach ($transactions as $transaction) {
            if ($transaction->isFullyAllocated()) {
                continue;
            }

            $customer = $transaction->getCustomer();
            $customerId = $customer->getId();
            $order = $transaction->getSalesOrder();
            $orderId = $order ? $order->getId() : '';

            if (empty($index[$customerId][$orderId])) {
                $index[$customerId][$orderId] = new MatchHelper($customer, $order);
            }
            $helper = $index[$customerId][$orderId];
            $helper->addTransaction($transaction);
        }
        return $this->flattenIndex($index);
    }

    private function flattenIndex(array $index)
    {
        $flattened = [];
        foreach ($index as $customerId => $byCustomer) {
            foreach ($byCustomer as $orderId => $helper) {
                $flattened = array_merge($flattened, $helper->getMatches());
            }
        }
        return $flattened;
    }
}
