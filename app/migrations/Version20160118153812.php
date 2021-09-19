<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * mantis4481: Fix GLTrans data.
 */
class Version20160118153812 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("set session sql_mode = ''");
        $this->addSql("
            update GLTrans t
            join Periods p on t.PeriodNo = p.PeriodNo
            set t.TranDate = concat(p.LastDate_in_Period, ' 23:59:00')
            where t.TranDate = 0
        ");

        $this->addSql("
            update GLTrans e
            join StockMove m on m.GLTransDR = e.CounterIndex
            set e.TypeNo = m.systemTypeNumber
            where e.TypeNo != m.systemTypeNumber
        ");

        $this->addSql("
            update GLTrans e
            join StockMove m on m.GLTransCR = e.CounterIndex
            set e.TypeNo = m.systemTypeNumber
            where e.TypeNo != m.systemTypeNumber
        ");
    }

    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
