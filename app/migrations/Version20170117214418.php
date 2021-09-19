<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add binStyle column to ReturnedItem
 */
class Version20170117214418 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ReturnedItem ADD binStyle VARCHAR(20) NULL AFTER binId');
        $this->addSql("UPDATE ReturnedItem SET binStyle = 'bin'");
        $this->addSql("
            UPDATE ReturnedItem item 
            JOIN StockSerialItems bin ON item.binId = bin.SerialNo
            SET item.binStyle = bin.BinStyle
        ");
        $this->addSql('ALTER TABLE ReturnedItem MODIFY binStyle VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE ReturnedItem ADD CONSTRAINT FK_509A20F3EF627FC0 FOREIGN KEY (binStyle) REFERENCES Stock_BinStyle (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE ReturnedItem DROP FOREIGN KEY FK_509A20F3EF627FC0');
        $this->addSql('ALTER TABLE ReturnedItem DROP binStyle');
    }
}
