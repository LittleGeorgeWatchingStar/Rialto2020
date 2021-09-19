<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Cascade deletes of SalesReturn to SalesReturnItem.
 */
class Version20170913183735_cascadeSalesReturnDelete extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE SalesReturnItem DROP FOREIGN KEY FK_D9476D1EDC4E6713');
        $this->addSql('ALTER TABLE SalesReturnItem ADD CONSTRAINT FK_D9476D1EDC4E6713 FOREIGN KEY (salesReturn) REFERENCES SalesReturn (id) ON DELETE CASCADE');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
