<?php

namespace Rialto\Accounting\Debtor\Match;

use Rialto\Accounting\Debtor\DebtorTransaction;

/**
 * @author Ian Phillips <ian@gumstix.com>
 */
class ManyToManyDebtorTransactionMatcher extends DebtorTransactionMatcher
{
    /**
     * @param DebtorTransaction[] $transactions
     * @return TransactionMatch[]
     */
    public function findMatches(array $transactions)
    {
        /** @var TransactionMatch[][] $index */
        $index = [];
        foreach ($transactions as $transaction) {
            if ($transaction->isFullyAllocated()) {
                continue;
            }

            $customer = $transaction->getCustomer();
            $customerId = $customer->getId();
            $order = $transaction->isInvoice() ?
                $transaction->getSalesOrder() : null;
            $orderId = $order ? $order->getId() : '';

            if (empty($index[$customerId][$orderId])) {
                $index[$customerId][$orderId] = new TransactionMatch($customer, $order);
            }
            $match = $index[$customerId][$orderId];
            $match->addTransaction($transaction);
        }
        return $this->flattenIndex($index);
    }

    /**
     * @param TransactionMatch[][] $index
     * @return TransactionMatch[]
     */
    private function flattenIndex(array $index)
    {
        $flattened = [];
        foreach ($index as $customerId => $byCustomer) {
            foreach ($byCustomer as $orderId => $match) {
                $key = $match->getIndexKey();
                $flattened[$key] = $match;
            }
        }
        return $flattened;
    }

}
