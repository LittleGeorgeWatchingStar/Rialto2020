<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add tables for Panels and PlacedBoards
 */
class Version20180223213830_addPanelizationTables extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE Panelization_Panel (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, margin NUMERIC(10, 4) NOT NULL, width NUMERIC(10, 4) NOT NULL, height NUMERIC(10, 4) NOT NULL, bottomLeft VARCHAR(255) NOT NULL COMMENT \'(DC2Type:vector2d)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Panelization_PlacedBoard (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, panelIndex SMALLINT NOT NULL, position VARCHAR(255) NOT NULL COMMENT \'(DC2Type:vector2d)\', rotation SMALLINT NOT NULL, workOrderId BIGINT UNSIGNED NOT NULL, panelId BIGINT UNSIGNED NOT NULL, UNIQUE INDEX UNIQ_8BFFC6AA67546C0A (workOrderId), INDEX IDX_8BFFC6AAB2FF7EFE (panelId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE Panelization_PlacedBoard ADD CONSTRAINT FK_8BFFC6AA67546C0A FOREIGN KEY (workOrderId) REFERENCES StockProducer (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE Panelization_PlacedBoard ADD CONSTRAINT FK_8BFFC6AAB2FF7EFE FOREIGN KEY (panelId) REFERENCES Panelization_Panel (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE Panelization_PlacedBoard');
        $this->addSql('DROP TABLE Panelization_Panel');
    }
}
