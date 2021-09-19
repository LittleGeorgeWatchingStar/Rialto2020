<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151023140507 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            update GoodsReceivedNotice grn
            join GoodsReceivedItem gi on gi.grnID = grn.BatchID
            join StockProducer p on gi.producerID = p.id
            left join PurchOrders po on grn.PurchaseOrderNo = po.OrderNo
            set grn.PurchaseOrderNo = p.purchaseOrderID
            where po.OrderNo is null
        ");

        $this->addSql('ALTER TABLE GoodsReceivedNotice DROP FOREIGN KEY GoodsReceivedNotice_ibfk_1');
        $this->addSql('ALTER TABLE GoodsReceivedNotice DROP FOREIGN KEY GoodsReceivedNotice_fk_systemTypeID');

        $this->addSql('DROP INDEX BatchID ON GoodsReceivedNotice');
        $this->addSql('DROP INDEX PurchaseOrderNo ON GoodsReceivedNotice');
        $this->addSql('DROP INDEX ReceivedBy ON GoodsReceivedNotice');
        $this->addSql('DROP INDEX GoodsReceivedNotice_fk_systemTypeID ON GoodsReceivedNotice');

        $this->addSql('ALTER TABLE GoodsReceivedNotice CHANGE PurchaseOrderNo PurchaseOrderNo BIGINT UNSIGNED NOT NULL, CHANGE DeliveryDate DeliveryDate DATETIME NOT NULL, CHANGE ReceivedBy ReceivedBy VARCHAR(20) NOT NULL, CHANGE systemTypeID systemTypeID SMALLINT NOT NULL');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();

    }
}
