<?php

namespace Rialto\Accounting\Bank\Statement\Parser;


use Rialto\Accounting\Bank\Statement\BankStatement;

/**
 * Used by StatementParser to capture the result of attempting to parse
 * a single row in an uploaded bank statement.
 */
class ParseResult
{
    /** @var array  */
    public $row;

    /** @var string */
    public $status = 'ignored';

    /** @var BankStatement */
    public $statement;

    /**
     * The reason this row was ignored.
     *
     * @var string|null
     */
    private $reason = null;

    public function __construct($row)
    {
        $this->row = $row;
    }

    public function setStatement(BankStatement $statement)
    {
        $this->statement = $statement;
        $this->status = $statement->isNew() ? 'new' : 'skipped';
    }

    public function isIgnored()
    {
        return 'ignored' == $this->status;
    }

    public function isSkipped()
    {
        return 'skipped' == $this->status;
    }

    public function setReason($reason)
    {
        $this->reason = $reason;
    }

    public function getReason()
    {
        return $this->reason;
    }
}
