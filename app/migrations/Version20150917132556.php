<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add binSize to PurchasingDataTemplate
 */
class Version20150917132556 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX id ON PurchasingDataTemplate');
        $this->addSql('ALTER TABLE PurchasingDataTemplate DROP FOREIGN KEY PurchasingDataTemplate_fk_binStyle');
        $this->addSql('ALTER TABLE PurchasingDataTemplate DROP FOREIGN KEY PurchasingDataTemplate_fk_supplierID');
        $this->addSql('DROP INDEX PurchasingDataTemplate_fk_supplierID ON PurchasingDataTemplate');
        $this->addSql('DROP INDEX PurchasingDataTemplate_fk_binStyle ON PurchasingDataTemplate');

        $this->addSql('ALTER TABLE PurchasingDataTemplate ADD binSize INT UNSIGNED NOT NULL DEFAULT 0, CHANGE strategy strategy VARCHAR(50) NOT NULL, CHANGE supplierID supplierID BIGINT UNSIGNED NOT NULL, CHANGE incrementQty incrementQty INT UNSIGNED NOT NULL');
        $this->addSql('update PurchasingDataTemplate set binSize = incrementQty');

        $this->addSql('ALTER TABLE PurchasingDataTemplate CHANGE binSize binSize INT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE PurchasingDataTemplate ADD CONSTRAINT FK_4CE46AB0E46B811B FOREIGN KEY (supplierID) REFERENCES Suppliers (SupplierID)');
        $this->addSql('ALTER TABLE PurchasingDataTemplate ADD CONSTRAINT FK_4CE46AB0EF627FC0 FOREIGN KEY (binStyle) REFERENCES BinStyle (name)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
