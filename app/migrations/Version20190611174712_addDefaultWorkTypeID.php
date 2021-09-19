<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20190611174712_addDefaultWorkTypeID extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE StockMaster ADD defaultWorkTypeID VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE StockMaster ADD CONSTRAINT FK_BA11D95A9656ACB7 FOREIGN KEY (defaultWorkTypeID) REFERENCES WorkType (id) ON DELETE RESTRICT');
        $this->addSql('CREATE INDEX IDX_BA11D95A9656ACB7 ON StockMaster (defaultWorkTypeID)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('ALTER TABLE StockMaster DROP FOREIGN KEY FK_BA11D95A9656ACB7');
        $this->addSql('ALTER TABLE StockMaster DROP defaultWorkTypeID');
    }
}
