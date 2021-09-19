<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Remove address fields from Locations and recreate foreign keys.
 */
class Version20151001130658 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Locations DROP Addr1, DROP Addr2, DROP MailStop, DROP City, DROP State, DROP Zip, DROP Country');
        $this->addSql('ALTER TABLE Locations ADD CONSTRAINT FK_9517C819AB3682CB FOREIGN KEY (SupplierID) REFERENCES Suppliers (SupplierID) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE Locations ADD CONSTRAINT FK_9517C819D5289B7F FOREIGN KEY (addressId) REFERENCES Geography_Address (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE Locations ADD CONSTRAINT FK_9517C8192B806C26 FOREIGN KEY (parentID) REFERENCES Locations (LocCode) ON DELETE RESTRICT');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9517C819AB3682CB ON Locations (SupplierID)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
