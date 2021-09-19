<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Fix invalid ECCN codes.
 */
class Version20160526121141 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("update StockMaster set ECCN_Code = '' where ECCN_Code = 'none'");
        $this->addSql("update StockMaster set ECCN_Code = 'EAR99' where ECCN_Code = 'EAR99.A1'");
        $this->addSql("update StockMaster set ECCN_Code = '5A002.A1' where ECCN_Code = '5A002a.1'");
        $this->addSql("update StockMaster set ECCN_Code = '4A003.C' where ECCN_Code = '4A003.c NLR'");
        $this->addSql("update StockMaster set ECCN_Code = upper(ECCN_Code)");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // no-op
    }
}
