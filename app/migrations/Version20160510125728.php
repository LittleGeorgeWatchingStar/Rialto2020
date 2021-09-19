<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Remove inheritance from PrintJob.
 */
class Version20160510125728 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE PrintJob ADD description VARCHAR(255) DEFAULT \'\' NOT NULL, CHANGE format format VARCHAR(40) NOT NULL');
        $this->addSql("update PrintJob set format = 'application/postscript' where format = 'raw'");
        $this->addSql("update PrintJob set format = 'application/pdf' where format = 'pdf'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("update PrintJob set format = 'raw' where format = 'application/postscript'");
        $this->addSql("update PrintJob set format = 'pdf' where format = 'application/pdf'");
        $this->addSql('ALTER TABLE PrintJob DROP description, CHANGE format format VARCHAR(20) NOT NULL');
    }
}
