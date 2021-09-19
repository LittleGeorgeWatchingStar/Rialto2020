<?php

namespace Rialto\Payment\Sweep\Email;

use Rialto\Accounting\Card\CardTransaction;
use Rialto\Email\Subscription\SubscriptionEmail;
use Rialto\Payment\PaymentMethod\PaymentMethodGroup;

/**
 * This email is sent by the SweepCardTransactionsCommand.
 *
 * It shows which card transactions were swept into the bank account.
 */
class SweepEmail extends SubscriptionEmail
{
    const TOPIC = 'payment.sweep';

    /**
     * @param CardTransaction[] $transactions
     * @param PaymentMethodGroup[] $groups
     */
    public function __construct(array $transactions, array $groups)
    {
        $this->params = [
            'transactions' => $transactions,
            'groups' => $groups,
            'totals' => $this->calculateTotals($transactions, $groups),
        ];
        $this->template = "payment/sweep/email.html.twig";
        $this->subject = 'Card Transaction Posting';
    }

    protected function getSubscriptionTopic()
    {
        return self::TOPIC;
    }

    /**
     * @param CardTransaction[] $transactions
     * @param PaymentMethodGroup[] $groups
     * @return float[]
     */
    private function calculateTotals($transactions, $groups)
    {
        $totals = [];
        foreach ($groups as $group) {
            $totals[$group->getId()] = 0;
        }
        foreach ($transactions as $trans) {
            $group = $trans->getPaymentMethodGroup();
            $totals[$group->getId()] += $trans->getAmountCaptured();
        }

        return $totals;
    }
}
