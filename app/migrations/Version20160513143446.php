<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add Manufacturer.supplierId
 */
class Version20160513143446 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Manufacturer ADD supplierId BIGINT UNSIGNED DEFAULT NULL');
        $this->addSql('ALTER TABLE Manufacturer ADD CONSTRAINT FK_253B3D24DF05A1D3 FOREIGN KEY (supplierId) REFERENCES Suppliers (SupplierID)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_253B3D24DF05A1D3 ON Manufacturer (supplierId)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Manufacturer DROP FOREIGN KEY FK_253B3D24DF05A1D3');
        $this->addSql('DROP INDEX UNIQ_253B3D24DF05A1D3 ON Manufacturer');
        $this->addSql('ALTER TABLE Manufacturer DROP supplierId');
    }
}
