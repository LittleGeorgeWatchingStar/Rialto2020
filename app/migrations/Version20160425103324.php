<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160425103324 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Requirement CHANGE designators designators LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');
        $this->addSql('ALTER TABLE BOM CHANGE Designators Designators LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\'');

        $this->addSql("update Requirement set designators = null where designators = ''");
        $this->addSql("update BOM set Designators = null where Designators = ''");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE BOM CHANGE Designators Designators VARCHAR(1000) NOT NULL COLLATE utf8_unicode_ci');
        $this->addSql('ALTER TABLE Requirement CHANGE designators designators VARCHAR(1000) DEFAULT \'\' NOT NULL COLLATE utf8_unicode_ci');
    }
}
