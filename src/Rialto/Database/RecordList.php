<?php


namespace Rialto\Database;


interface RecordList extends \IteratorAggregate
{
    /** Max number of records to show on a page */
    const MAX_RECORDS = 100;
    /**
     * The maximum number of records that can be requested, to prevent
     * denial of service attacks.
     */
    const HARD_MAX_RECORDS = 1000;

    public function limit();

    public function total();
}
