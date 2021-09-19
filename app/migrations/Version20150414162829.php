<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150414162829 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Shipping_HarmonizationCode (id VARCHAR(10) NOT NULL, name VARCHAR(255) DEFAULT \'\' NOT NULL, description VARCHAR(255) DEFAULT \'\' NOT NULL, active TINYINT(1) DEFAULT \'1\' NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE StockMaster ADD harmonizationCode VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE StockMaster ADD CONSTRAINT FK_BA11D95A28335B47 FOREIGN KEY (harmonizationCode) REFERENCES Shipping_HarmonizationCode (id) ON DELETE RESTRICT');
        $this->addSql('CREATE INDEX IDX_BA11D95A28335B47 ON StockMaster (harmonizationCode)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE Shipping_HarmonizationCode');
        $this->addSql('ALTER TABLE StockMaster DROP harmonizationCode');
    }
}
