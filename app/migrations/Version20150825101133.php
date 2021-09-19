<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Fix a bad transfer
 */
class Version20150825101133 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            update LocTransfers set RecQty = 0, RecDate = null
            where Reference = 28124
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // pass
    }
}
