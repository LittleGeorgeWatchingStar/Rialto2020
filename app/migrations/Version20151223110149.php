<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add productUrl to PurchData
 */
class Version20151223110149 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE PurchData DROP FOREIGN KEY FK_BBC91716141D96');
        $this->addSql('ALTER TABLE PurchData DROP FOREIGN KEY FK_BBC917399B8EF6');
        $this->addSql('ALTER TABLE PurchData DROP FOREIGN KEY FK_BBC917CF9430DB');
        $this->addSql('ALTER TABLE PurchData DROP FOREIGN KEY PurchData_fk_manufacturerID');
        $this->addSql('ALTER TABLE PurchData DROP FOREIGN KEY PurchData_fk_SupplierNo');
        $this->addSql('DROP INDEX StockID ON PurchData');
        $this->addSql('DROP INDEX SupplierNo ON PurchData');
        $this->addSql('DROP INDEX PurchData_fk_LocCode ON PurchData');
        $this->addSql('DROP INDEX PurchData_fk_manufacturerID ON PurchData');
        $this->addSql('DROP INDEX SupplierNo_LocCode_CatalogNo_QuotationNo ON PurchData');

        $this->addSql('ALTER TABLE PurchData
            ADD productUrl VARCHAR(255) DEFAULT \'\' NOT NULL,
            DROP Price,
            DROP Labourcost,
            DROP LeadTime,
            DROP AutoManufacture');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
