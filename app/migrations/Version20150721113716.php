<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Tidy up StockAllocation, step 2.
 */
class Version20150721113716 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE StockAllocation ADD CONSTRAINT FK_4EDD5991B069402A FOREIGN KEY (requirementID) REFERENCES Requirement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE StockAllocation ADD CONSTRAINT FK_4EDD5991399B8EF6 FOREIGN KEY (StockID) REFERENCES StockMaster (StockID) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_4EDD5991B069402A ON StockAllocation (requirementID)');
        $this->addSql('CREATE INDEX IDX_4EDD5991399B8EF6 ON StockAllocation (StockID)');
        $this->addSql('CREATE INDEX IDX_4EDD5991CFF077F9 ON StockAllocation (SourceNo)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
