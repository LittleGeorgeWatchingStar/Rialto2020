<?php

namespace Rialto\Accounting\Bank\Statement\Parser;


use Gumstix\Filetype\CsvFile;

interface BankStatementParser
{
    /** @return ParseResult[] */
    public function parse(CsvFile $file): array;

    public function getNumAdded(): int;

    public function getNumSkipped(): int;
}
