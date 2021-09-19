<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add SalesOrders.reasonForShipping to replace ExtraLanguage
 */
class Version20170120040840_add_reasonForShipping extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // Drop some useless indexes.
        $this->addSql('DROP INDEX OrderNo ON SalesOrders');
        $this->addSql('DROP INDEX OrderType ON SalesOrders');

        $this->addSql('ALTER TABLE SalesOrders ADD reasonForShipping VARCHAR(255) DEFAULT \'\' NOT NULL');

        $this->addSql("update SalesOrders set reasonForShipping = 'RMA' where ExtraLanguage = 1");
        $this->addSql("update SalesOrders set reasonForShipping = 'In Transit' where ExtraLanguage = 2");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE SalesOrders DROP reasonForShipping');
    }
}
