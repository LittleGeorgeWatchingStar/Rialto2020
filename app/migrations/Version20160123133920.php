<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Rename StockLevelStatus.netQtyAllocated to qtyAllocated
 */
class Version20160123133920 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE StockLevelStatus CHANGE netQtyAllocated qtyAllocated INT UNSIGNED NOT NULL');
        $this->addSql('DROP VIEW StockLevel');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE StockLevelStatus CHANGE qtyAllocated netQtyAllocated INT UNSIGNED NOT NULL');
    }
}
