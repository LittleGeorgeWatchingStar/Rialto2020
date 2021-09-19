<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20150610150327 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Core_Task (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, dateCreated DATETIME NOT NULL, roles LONGTEXT NULL COMMENT \'(DC2Type:simple_array)\', name VARCHAR(255) NOT NULL, routeName VARCHAR(255) NOT NULL, routeParams LONGTEXT NOT NULL COMMENT \'(DC2Type:json_array)\', status VARCHAR(50) DEFAULT \'\' NOT NULL, taskType VARCHAR(30) NOT NULL, entityId BIGINT UNSIGNED NOT NULL, INDEX IDX_4258358DF62829FC (entityId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE Core_Task');
    }
}
