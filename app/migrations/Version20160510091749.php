<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create "Printer" table.
 */
class Version20160510091749 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Printer (id VARCHAR(20) NOT NULL, host VARCHAR(100) NOT NULL, port SMALLINT UNSIGNED NOT NULL, printerType VARCHAR(10) NOT NULL, pageWidth INT UNSIGNED DEFAULT NULL, pageHeight INT UNSIGNED DEFAULT NULL, sleepTime SMALLINT UNSIGNED DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql("
            insert into Printer
            (id, host, port, printerType, pageWidth, pageHeight, sleepTime) VALUES 
            ('standard',     'sanjose.gumstix.net', 9100, 'standard', null, null, null),
            ('color',        'sanjose.gumstix.net', 9107, 'standard', null, null, null),
            ('label',        'sanjose.gumstix.net', 9101, 'label',    81,   252,  null),
            ('instructions', 'sanjose.gumstix.net', 9102, 'label',    81,   252,  null),
            ('ups',          'sanjose.gumstix.net', 9103, 'ups',      null, null, 5)
        ");

        $this->addSql('DROP INDEX id ON PrintJob');
        $this->addSql('ALTER TABLE PrintJob CHANGE printerID printerID VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE PrintJob ADD CONSTRAINT FK_9A8332D2D3DAAA4F FOREIGN KEY (printerID) REFERENCES Printer (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX IDX_9A8332D2D3DAAA4F ON PrintJob');
        $this->addSql('DROP TABLE Printer');
    }
}
