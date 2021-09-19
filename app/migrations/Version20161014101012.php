<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add manufacturerId to StockSerialItems.
 */
class Version20161014101012 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE StockSerialItems DROP FOREIGN KEY StockSerialItems_fk_BinStyle');
        $this->addSql('ALTER TABLE StockSerialItems DROP FOREIGN KEY StockSerialItems_fk_invoiceItemID');
        $this->addSql('ALTER TABLE StockSerialItems DROP FOREIGN KEY StockSerialItems_fk_customizationId');
        $this->addSql('ALTER TABLE StockSerialItems DROP FOREIGN KEY StockSerialItems_ibfk_1');
        $this->addSql('ALTER TABLE StockSerialItems DROP FOREIGN KEY StockSerialItems_ibfk_2');
        $this->addSql('DROP INDEX SerialNo ON StockSerialItems');
        $this->addSql('DROP INDEX StockID ON StockSerialItems');
        $this->addSql('DROP INDEX LocCode ON StockSerialItems');
        $this->addSql('DROP INDEX StockSerialItems_fk_BinStyle ON StockSerialItems');
        $this->addSql('DROP INDEX StockSerialItems_fk_customizationId ON StockSerialItems');

        $this->addSql('ALTER TABLE StockSerialItems DROP missingSince, CHANGE StockID StockID VARCHAR(20) NOT NULL, CHANGE LocCode LocCode VARCHAR(5) NOT NULL, DROP invoiceItemID, ADD manufacturerId BIGINT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE StockSerialItems ADD CONSTRAINT FK_C2D350B3399B8EF6 FOREIGN KEY (StockID) REFERENCES StockMaster (StockID) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE StockSerialItems ADD CONSTRAINT FK_C2D350B316141D96 FOREIGN KEY (BinStyle) REFERENCES BinStyle (name) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE StockSerialItems ADD CONSTRAINT FK_C2D350B3CF9430DB FOREIGN KEY (LocCode) REFERENCES Locations (LocCode) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE StockSerialItems ADD CONSTRAINT FK_C2D350B34E59B3A6 FOREIGN KEY (manufacturerId) REFERENCES Manufacturer (id) ON DELETE RESTRICT');

        $this->addSql("
            update StockSerialItems bin
            join PurchData pd 
            on bin.StockID = pd.StockID
            and bin.manufacturerCode = pd.ManufacturerCode
            set bin.manufacturerId = pd.manufacturerID
            where bin.manufacturerCode != ''
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
