<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add 'type' and 'flags' columns to Substitutions.
 */
class Version20160425122025 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("ALTER TABLE Substitutions ADD type VARCHAR(10) NOT NULL DEFAULT '' AFTER ID, ADD flags LONGTEXT DEFAULT NULL COMMENT '(DC2Type:simple_array)'");

        $this->addSql("
        UPDATE Substitutions
        SET type = 'SWAP'
        WHERE type = '' AND dnpDesignators IS NOT NULL AND addDesignators IS NOT NULL
        ");

        $this->addSql("
        UPDATE Substitutions
        SET type = 'DNP'
        WHERE type = '' AND dnpDesignators IS NOT NULL AND addDesignators IS NULL
        ");

        $this->addSql("
        UPDATE Substitutions
        SET type = 'ADD'
        WHERE type = '' AND dnpDesignators IS NULL AND addDesignators IS NOT NULL
        ");

        $this->addSql("UPDATE Substitutions SET flags = 'ext-temp' WHERE Instructions LIKE '%extended temp%'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Substitutions DROP type, DROP flags');
    }
}
