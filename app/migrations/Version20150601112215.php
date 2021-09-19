<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Fix "zero dates" in the Customer table.
 */
class Version20150601112215 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            update DebtorsMaster c
            join (
                SELECT b.DebtorNo, min(o.OrdDate) AS MinDate
                FROM CustBranch b
                JOIN SalesOrders o ON o.branchID = b.id
                group by b.DebtorNo
            ) as b on b.DebtorNo = c.DebtorNo
            set c.ClientSince = MinDate
            where c.ClientSince = 0
            and MinDate != 0
            and MinDate is not null
        ");

        $this->addSql("
            update DebtorsMaster c
            join (
                SELECT t.customerID as DebtorNo, min(t.TranDate) AS MinDate
                FROM DebtorTrans t
                group by t.customerID
            ) as b on b.DebtorNo = c.DebtorNo
            set c.ClientSince = MinDate
            where c.ClientSince = 0
            and MinDate != 0
            and MinDate is not null
        ");

        $this->addSql("
            update DebtorsMaster
            set ClientSince = (select min(LastDate_in_Period) from Periods)
            where ClientSince = 0
        ");

        /*
         * select count(*) from DebtorsMaster where ClientSince = 0
         * select count(*) from DebtorsMaster where ClientSince is null
         */
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
