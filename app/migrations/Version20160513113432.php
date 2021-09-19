<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add ROLE_PURCHASING_DATA.
 */
class Version20160513113432 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("replace into Role (id) values ('ROLE_PURCHASING_DATA')");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("delete from Role where id = 'ROLE_PURCHASING_DATA'");
    }
}
