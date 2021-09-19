<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Replace PurchOrders.autoAllocate with autoAddItems and add autoAllocateTo.
 */
class Version20180213002158_addAllocateTo extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE PurchOrders ADD autoAddItems TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('ALTER TABLE PurchOrders ADD autoAllocateTo TINYINT(1) DEFAULT \'1\' NOT NULL');

        // We will remove `autoAllocate` in a subsequent migration.
        $this->addSql("update PurchOrders set autoAddItems = autoAllocate");

        $this->addSql("update StockProducer set flags = concat(flags, ' ok_to_build') where type = 'labour'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE PurchOrders DROP autoAddItems, DROP autoAllocateTo');
    }
}
