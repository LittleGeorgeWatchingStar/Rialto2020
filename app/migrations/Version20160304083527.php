<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Widen StockMaster.PartValue and Temperature, plus cleanup.
 */
class Version20160304083527 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX Description ON StockMaster');
        $this->addSql('DROP INDEX LastCurCostDate ON StockMaster');
        $this->addSql('DROP INDEX MBflag ON StockMaster');
        $this->addSql('DROP INDEX StockID ON StockMaster');
        $this->addSql('DROP INDEX Controlled ON StockMaster');
        $this->addSql('DROP INDEX DiscountCategory ON StockMaster');
        $this->addSql('DROP INDEX CategoryID ON StockMaster');
        $this->addSql('ALTER TABLE StockMaster DROP FOREIGN KEY StockMaster_fk_currentStandardCost');
        $this->addSql('DROP INDEX StockMaster_fk_currentStandardCost ON StockMaster');

        $this->addSql('
ALTER TABLE StockMaster
CHANGE StockID StockID VARCHAR(20) NOT NULL,
CHANGE Flags Flags VARCHAR(32) DEFAULT \'\' NOT NULL,
CHANGE PartValue PartValue VARCHAR(50) DEFAULT \'\' NOT NULL,
CHANGE Origin Origin VARCHAR(20) NOT NULL,
CHANGE Temperature Temperature VARCHAR(50) DEFAULT \'\' NOT NULL');

        $this->addSql('ALTER TABLE StockMaster ADD CONSTRAINT FK_BA11D95AE8042869 FOREIGN KEY (CategoryID) REFERENCES StockCategory (CategoryID)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
