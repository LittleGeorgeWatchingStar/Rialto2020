<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Fix StockProducer dates
 */
class Version20150806161311 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        /*
        select dateCreated, dateReleased, dateUpdated, dateClosed, qtyOrdered, qtyReceived
        from StockProducer where dateUpdated is null
        */

        $this->addSql("
            update PurchOrders
            set OrdDate = DatePrinted
            where (OrdDate is null or OrdDate = 0)
            and DatePrinted is not null
            and DatePrinted != 0
        ");

        $this->addSql("
            update StockProducer p
            join PurchOrders po
                on p.purchaseOrderID = po.OrderNo
            set p.dateCreated = po.OrdDate
            where p.dateCreated = 0
            and po.OrdDate is not null
            and po.OrdDate != 0
        ");

        $this->addSql("
            update StockProducer
            set dateUpdated = dateClosed
            where dateUpdated is null
            and dateClosed is not null
            and dateClosed != 0
        ");

        $this->addSql("
            update StockProducer
            set dateUpdated = dateReleased
            where dateUpdated is null
            and dateReleased is not null
            and dateReleased != 0
        ");

        $this->addSql("
            update StockProducer
            set dateUpdated = dateCreated
            where dateUpdated is null
            and dateCreated is not null
            and dateReleased != 0
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();

    }
}
