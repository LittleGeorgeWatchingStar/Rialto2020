<?php

namespace Rialto\Accounting\Balance;

/**
 * A collection of BalanceUpdates.
 */
class BalanceUpdateList
{
    /** @var BalanceUpdate[] */
    private $updates = [];

    /**
     * @param AccountBalance[] $before
     */
    public function setBefore(array $before)
    {
        foreach ( $before as $balance ) {
            $key = $this->getIndexKey($balance);
            $update = new BalanceUpdate($balance);
            $this->updates[$key] = $update;
        }
    }

    private function getIndexKey(AccountBalance $balance)
    {
        return sprintf('%s-%s', $balance->getAccountCode(), $balance->getPeriodNo());
    }

    /**
     * @param AccountBalance[] $before
     */
    public function setAfter(array $after)
    {
        foreach ( $after as $balance ) {
            $key = $this->getIndexKey($balance);
            $update = $this->updates[$key];
            $update->setAfter($balance);
        }
    }

    public function getUpdates()
    {
        return $this->updates;
    }
}
