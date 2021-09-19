<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Tidy up StockAllocation, step 1.
 */
class Version20150721113505 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX AllocationID ON StockAllocation');
        $this->addSql('ALTER TABLE StockAllocation DROP FOREIGN KEY StockAllocation_fk_StockID');
        $this->addSql('DROP INDEX StockAllocation_fk_StockID ON StockAllocation');
        $this->addSql('ALTER TABLE StockAllocation DROP requestID, CHANGE requirementID requirementID BIGINT UNSIGNED NOT NULL, CHANGE SourceType SourceType VARCHAR(30) NOT NULL, CHANGE SourceNo SourceNo BIGINT UNSIGNED NOT NULL, CHANGE StockID StockID VARCHAR(20) NOT NULL, CHANGE Qty Qty INT UNSIGNED NOT NULL, CHANGE Delivered Delivered INT UNSIGNED DEFAULT 0 NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
