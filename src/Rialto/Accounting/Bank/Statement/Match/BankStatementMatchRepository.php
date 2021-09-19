<?php

namespace Rialto\Accounting\Bank\Statement\Match;

use Rialto\Accounting\Bank\Statement\BankStatement;
use Rialto\Accounting\Bank\Statement\BankStatementMatch;
use Rialto\Accounting\Bank\Transaction\BankTransaction;
use Rialto\Database\Orm\RialtoRepositoryAbstract;

class BankStatementMatchRepository extends RialtoRepositoryAbstract
{
    public function findOrCreate(BankStatement $statement, BankTransaction $trans)
    {
        $match = $this->findOneBy([
            'bankStatement' => $statement->getId(),
            'bankTransaction' => $trans->getId(),
        ]);
        if (! $match ) {
            $match = new BankStatementMatch($statement, $trans);
        }
        return $match;
    }
}
