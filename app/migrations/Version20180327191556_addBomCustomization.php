<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add a customization column to the BOM table.
 */
class Version20180327191556_addBomCustomization extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE BOM DROP FOREIGN KEY BOM_fk_Component');
        $this->addSql('ALTER TABLE BOM DROP FOREIGN KEY BOM_fk_Parent_ParentVersion');
        $this->addSql('DROP INDEX ID ON BOM');
        $this->addSql('DROP INDEX component ON BOM');
        $this->addSql('DROP INDEX parent_parentversion_component ON BOM');

        $this->addSql('ALTER TABLE BOM ADD customizationID BIGINT UNSIGNED DEFAULT NULL');

        $this->addSql('ALTER TABLE BOM ADD CONSTRAINT FK_F3D3EE5B3A22657975FF85EF FOREIGN KEY (Parent, ParentVersion) REFERENCES ItemVersion (stockCode, version)');
        $this->addSql('ALTER TABLE BOM ADD CONSTRAINT FK_F3D3EE5BCB0F23F4 FOREIGN KEY (Component) REFERENCES StockMaster (StockID) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE BOM ADD CONSTRAINT FK_F3D3EE5BB81D610D FOREIGN KEY (customizationID) REFERENCES Customization (id) ON DELETE RESTRICT');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F3D3EE5B3A22657975FF85EFCB0F23F4 ON BOM (Parent, ParentVersion, Component)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE BOM DROP FOREIGN KEY FK_F3D3EE5BB81D610D');
        $this->addSql('ALTER TABLE BOM DROP customizationID');
    }
}
