<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add PurchOrders.editNo for concurrency control.
 */
class Version20160519155353 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE PurchOrders ADD editNo INT UNSIGNED DEFAULT 0 NOT NULL, ADD dateUpdated DATETIME NULL, ADD autoAllocate TINYINT(1) DEFAULT \'1\' NOT NULL');
        $this->addSql('UPDATE PurchOrders set dateUpdated = greatest(OrdDate, ifnull(DatePrinted, OrdDate))');
        $this->addSql("
        update PurchOrders po
        join StockProducer sp on sp.purchaseOrderId = po.OrderNo
        set po.dateUpdated = greatest(po.dateUpdated, ifnull(sp.dateUpdated, po.dateUpdated))
        ");
        $this->addSql('ALTER TABLE PurchOrders MODIFY dateUpdated DATETIME NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE PurchOrders DROP editNo, DROP dateUpdated, DROP autoAllocate');
    }
}
