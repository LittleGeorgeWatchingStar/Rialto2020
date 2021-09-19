<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Rebuild PurchData indexes.
 */
class Version20151223111010 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE PurchData ADD CONSTRAINT FK_BBC917399B8EF6 FOREIGN KEY (StockID) REFERENCES StockMaster (StockID)');
        $this->addSql('ALTER TABLE PurchData ADD CONSTRAINT FK_BBC91748CBED4C FOREIGN KEY (SupplierNo) REFERENCES Suppliers (SupplierID)');
        $this->addSql('ALTER TABLE PurchData ADD CONSTRAINT FK_BBC917CF9430DB FOREIGN KEY (LocCode) REFERENCES Locations (LocCode)');
        $this->addSql('ALTER TABLE PurchData ADD CONSTRAINT FK_BBC91716141D96 FOREIGN KEY (BinStyle) REFERENCES BinStyle (name)');
        $this->addSql('ALTER TABLE PurchData ADD CONSTRAINT FK_BBC9177537936E FOREIGN KEY (manufacturerID) REFERENCES Manufacturer (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BBC91748CBED4CCF9430DB7301D70D2BC2E4C ON PurchData (SupplierNo, LocCode, CatalogNo, QuotationNo)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
