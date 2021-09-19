<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Change shelving system to use one-based (not zero-based) indexing.
 */
class Version20170130181711_change_shelfOneBasedIndexing extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            update Stock_Shelf set indexNo = indexNo + 1
        ");
        $this->addSql("
            update Stock_ShelfPosition set x = x + 1, y = y + 1, z = z + 1
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
