<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190930154308_removeCostTemplateTable extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE PurchasingCostTemplate');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE PurchasingCostTemplate (id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, strategyID BIGINT UNSIGNED DEFAULT 0 NOT NULL, minimumOrderQty INT UNSIGNED DEFAULT 0 NOT NULL, manufacturerLeadTime SMALLINT UNSIGNED DEFAULT 0 NOT NULL, supplierLeadTime SMALLINT UNSIGNED DEFAULT NULL, binSize INT UNSIGNED DEFAULT 0 NOT NULL, unitCost NUMERIC(16, 4) DEFAULT \'0.0000\' NOT NULL, UNIQUE INDEX strategyID_minimumOrderQty_leadTime (strategyID, minimumOrderQty, manufacturerLeadTime), INDEX IDX_6E81B47EFCB54FC2 (strategyID), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE PurchasingCostTemplate ADD CONSTRAINT PurchasingCostTemplate_fk_strategyID FOREIGN KEY (strategyID) REFERENCES PurchasingDataTemplate (id) ON DELETE CASCADE');
    }
}
