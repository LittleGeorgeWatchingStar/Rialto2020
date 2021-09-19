<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Tidy up Requirement indexes, step 2.
 */
class Version20150721111318 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE Requirement ADD CONSTRAINT FK_5DA3DA87EC2233CA FOREIGN KEY (stockCode) REFERENCES StockMaster (StockID)');
        $this->addSql('ALTER TABLE Requirement ADD CONSTRAINT FK_5DA3DA87B81D610D FOREIGN KEY (customizationID) REFERENCES Customization (id)');
        $this->addSql('CREATE INDEX IDX_5DA3DA87EC2233CA ON Requirement (stockCode)');
        $this->addSql('CREATE INDEX IDX_5DA3DA87B81D610D ON Requirement (customizationID)');
        $this->addSql('CREATE INDEX IDX_5DA3DA8773AB040B ON Requirement (consumerID)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
