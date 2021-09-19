<?php


namespace Rialto\Accounting\Transaction;

/**
 * Classes that implement this interface have lifecycle events that result
 * in accounting transactions being created.
 */
interface TransactionInitiator
{
    /**
     * @return string
     */
    public function getSystemTypeId();

    /**
     * @return string|null
     */
    public function getGroupNo();

    /**
     * @return string A human-friendly string describing the source
     */
    public function getMemo();
}
