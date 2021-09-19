<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Fix the shipping and auto-build versions of existing modules
 */
class Version20150914120309 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            update StockMaster item
            join (
                select stockCode, max(cast(version as unsigned)) as maxver
                from ItemVersion
                where stockCode like 'MOD%'
                and version regexp '^[0-9]+$'
                group by stockCode
                having maxver != 0
            ) as mv on mv.stockCode = item.StockID
            set item.ShippingVersion = mv.maxver,
                item.AutoBuildVersion = mv.maxver
        ");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();

    }
}
