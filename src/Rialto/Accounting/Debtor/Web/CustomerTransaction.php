<?php

namespace Rialto\Accounting\Debtor\Web;

use Rialto\Database\Orm\EntityList;

class CustomerTransaction
{
    /*
     * Create transaction from given file data
     *
     * @param mixed $file
     * @param EntityRepository|ObjectRepository $repo
     * @param EventDispatcherInterface|object $eventDispatch
     * @param DoctrineDbManager $dbm
     *
     * @return string
     */
    static public function createTransaction($file, $repo)
    {
        $lines = array_slice(file($file), 4);
        $amount = 0;
        $date = '';
        $transId = '';
        $ret = array();
        foreach ($lines as $index => $line) {
            $line = explode("\t", $line);
            $index++;
            $line = [
                'date' => $line[0],
                'orderId' => $line[1],
                'sku' => $line[2],
                'transactionType' => $line[3],
                'paymentType' => $line[4],
                'paymentDetail' => $line[5],
                'amount' => (float)str_replace('$', '', $line[6]),
                'quantity' => (int)$line[7],
                'productTitle' => $line[8],
            ];
            // Only process Order Payment with valild Order ID and price > 0
            if ($line['orderId'] && strpos($line['transactionType'], 'Payment') !== false
                && $line['amount'] > 0) {
                $transId = $transId == '' ? $line['orderId'] : $transId;
                // Multiply the payment amount with quantity to get totals
                if ($line['quantity'] > 0) {
                    $line['amount'] = $line['amount'] * $line['quantity'];
                }
                // Group the same orders
                if ($line['orderId'] == $transId && $index !== count($lines)) {
                    $date = $line['date'];
                    $amount += $line['amount'];
                } else {
                    // Adding the last item
                    if ($index === count($lines)) {
                        $date = $line['date'];
                        $amount += $line['amount'];
                    }
                    $orders = new EntityList($repo, ['reference' => $transId, 'item' => $line['sku']]);
                    if ($orders->total() === 0) {
                        continue;
                    }
                    array_push($ret, [$orders, $amount, $date]);
                    $transId = $line['orderId'];
                    $amount = $line['amount'];
                    $date = $line['date'];
                }
            }
        }
        return $ret;
    }
}
