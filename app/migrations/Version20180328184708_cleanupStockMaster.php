<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Make sure that StockMaster.Controlled and .CloseCount are not null.
 */
class Version20180328184708_cleanupStockMaster extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("update StockMaster set CloseCount = 0 where CloseCount is null");
        $this->addSql('ALTER TABLE StockMaster CHANGE Controlled Controlled TINYINT(1) DEFAULT \'1\' NOT NULL, CHANGE CloseCount CloseCount TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
