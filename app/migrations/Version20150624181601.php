<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150624181601 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE ReturnedItem (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, dateCreated DATETIME NOT NULL, manufacturerCode VARCHAR(255) DEFAULT \'\' NOT NULL, catalogNumber VARCHAR(255) DEFAULT \'\' NOT NULL, supplierReference VARCHAR(255) DEFAULT \'\' NOT NULL, quantity INT UNSIGNED NOT NULL, binId BIGINT UNSIGNED DEFAULT NULL, returnedFromId VARCHAR(5) NOT NULL, returnedToId VARCHAR(5) NOT NULL, stockCode VARCHAR(20) DEFAULT NULL, buildPO BIGINT UNSIGNED DEFAULT NULL, partsPO BIGINT UNSIGNED DEFAULT NULL, UNIQUE INDEX UNIQ_509A20F34AD3DC9E (binId), INDEX IDX_509A20F3F28D5E17 (returnedFromId), INDEX IDX_509A20F3BA46EBD2 (returnedToId), INDEX IDX_509A20F3EC2233CA (stockCode), INDEX IDX_509A20F32C7E21EE (buildPO), INDEX IDX_509A20F3DF8C8092 (partsPO), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ReturnedItem ADD CONSTRAINT FK_509A20F34AD3DC9E FOREIGN KEY (binId) REFERENCES StockSerialItems (SerialNo)');
        $this->addSql('ALTER TABLE ReturnedItem ADD CONSTRAINT FK_509A20F3F28D5E17 FOREIGN KEY (returnedFromId) REFERENCES Locations (LocCode)');
        $this->addSql('ALTER TABLE ReturnedItem ADD CONSTRAINT FK_509A20F3BA46EBD2 FOREIGN KEY (returnedToId) REFERENCES Locations (LocCode)');
        $this->addSql('ALTER TABLE ReturnedItem ADD CONSTRAINT FK_509A20F3EC2233CA FOREIGN KEY (stockCode) REFERENCES StockMaster (StockID)');
        $this->addSql('ALTER TABLE ReturnedItem ADD CONSTRAINT FK_509A20F32C7E21EE FOREIGN KEY (buildPO) REFERENCES PurchOrders (OrderNo)');
        $this->addSql('ALTER TABLE ReturnedItem ADD CONSTRAINT FK_509A20F3DF8C8092 FOREIGN KEY (partsPO) REFERENCES PurchOrders (OrderNo)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE ReturnedItem');
    }
}
