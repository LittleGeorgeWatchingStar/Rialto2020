<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add Locations.addressId.
 */
class Version20151001114443 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Locations DROP FOREIGN KEY Locations_fk_SupplierID');
        $this->addSql('ALTER TABLE Locations DROP FOREIGN KEY Locations_fk_parentID');
        $this->addSql('DROP INDEX SupplierID ON Locations');
        $this->addSql('DROP INDEX Locations_fk_parentID ON Locations');
        $this->addSql('ALTER TABLE Locations ADD addressId BIGINT UNSIGNED DEFAULT NULL, CHANGE LocCode LocCode VARCHAR(5) NOT NULL, CHANGE LocationName LocationName VARCHAR(50) NOT NULL, CHANGE Email Email VARCHAR(255) DEFAULT \'\' NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
