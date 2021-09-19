<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Tidy up Requirement indexes, step 1.
 */
class Version20150721110348 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP INDEX id ON Requirement');
        $this->addSql('ALTER TABLE Requirement DROP FOREIGN KEY StockRequest_fk_customizationID');
        $this->addSql('ALTER TABLE Requirement DROP FOREIGN KEY StockRequest_fk_stockCode');
        $this->addSql('DROP INDEX StockRequest_fk_stockCode ON Requirement');
        $this->addSql('DROP INDEX StockRequest_fk_customizationID ON Requirement');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
