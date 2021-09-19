<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add ROLE_STOCK_VIEW.
 */
class Version20160425092621 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("INSERT INTO Role (id) VALUES ('ROLE_STOCK_VIEW')");

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("DELETE FROM Role WHERE id = 'ROLE_STOCK_VIEW'");

    }
}
