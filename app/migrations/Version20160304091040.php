<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * mantis5413: record date and user who updated Manufacturer
 */
class Version20160304091040 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX id ON Manufacturer');
        $this->addSql('DROP INDEX name ON Manufacturer');

        $this->addSql('ALTER TABLE Manufacturer ADD dateUpdated DATETIME DEFAULT NULL, ADD updatedBy VARCHAR(20) DEFAULT NULL, CHANGE name name VARCHAR(255) NOT NULL, CHANGE conflictFilename conflictFilename VARCHAR(255) DEFAULT \'\' NOT NULL');

        $this->addSql('ALTER TABLE Manufacturer ADD CONSTRAINT FK_253B3D24E8DE7170 FOREIGN KEY (updatedBy) REFERENCES WWW_Users (UserID) ON DELETE RESTRICT');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_253B3D245E237E06 ON Manufacturer (name)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Manufacturer DROP dateUpdated, DROP updatedBy');
    }
}
