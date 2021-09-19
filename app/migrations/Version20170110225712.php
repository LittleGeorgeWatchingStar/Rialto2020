<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Refactor BinStyle
 */
class Version20170110225712 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE BinStyle CHANGE `name` `id` VARCHAR(20) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE BinStyle ADD COLUMN name VARCHAR(20) NULL UNIQUE KEY 
        ");
        $this->addSql("
            ALTER TABLE BinStyle RENAME Stock_BinStyle
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
