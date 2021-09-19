<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Require POs for all stock producers.
 */
class Version20151023120314 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql("update StockProducer set dateCreated = dateUpdated where dateCreated = 0");

        $this->addSql('ALTER TABLE PurchOrders DROP FOREIGN KEY FK_687035E61878BDD0');
        $this->addSql('ALTER TABLE PurchOrders DROP FOREIGN KEY FK_687035E648CBED4C');
        $this->addSql('ALTER TABLE PurchOrders DROP FOREIGN KEY FK_687035E64981FCE1');
        $this->addSql('ALTER TABLE PurchOrders DROP FOREIGN KEY FK_687035E6CCB9F89C');
        $this->addSql('ALTER TABLE PurchOrders DROP FOREIGN KEY FK_687035E6EA1C978');

        $this->addSql('DROP INDEX FK_687035E648CBED4C ON PurchOrders');
        $this->addSql('DROP INDEX FK_687035E6EA1C978 ON PurchOrders');
        $this->addSql('DROP INDEX FK_687035E64981FCE1 ON PurchOrders');
        $this->addSql('DROP INDEX FK_687035E6CCB9F89C ON PurchOrders');
        $this->addSql('DROP INDEX FK_687035E61878BDD0 ON PurchOrders');

        $this->addSql('ALTER TABLE StockProducer DROP FOREIGN KEY StockProducer_fk_locationID');
        $this->addSql('ALTER TABLE StockProducer DROP FOREIGN KEY StockProducer_fk_parentID');
        $this->addSql('ALTER TABLE StockProducer DROP FOREIGN KEY StockProducer_fk_purchaseOrderID');
        $this->addSql('ALTER TABLE StockProducer DROP FOREIGN KEY StockProducer_fk_stockCode');
        $this->addSql('ALTER TABLE StockProducer DROP FOREIGN KEY StockProducer_fk_customizationID');
        $this->addSql('ALTER TABLE StockProducer DROP FOREIGN KEY StockProducer_fk_glAccountID');
        $this->addSql('ALTER TABLE StockProducer DROP FOREIGN KEY StockProducer_fk_purchasingDataID');

        $this->addSql('DROP INDEX id ON StockProducer');
        $this->addSql('DROP INDEX workOrderID ON StockProducer');
        $this->addSql('DROP INDEX poItemID ON StockProducer');
        $this->addSql('DROP INDEX StockProducer_fk_locationID ON StockProducer');
        $this->addSql('DROP INDEX StockProducer_fk_stockCode ON StockProducer');
        $this->addSql('DROP INDEX StockProducer_fk_purchaseOrderID ON StockProducer');
        $this->addSql('DROP INDEX StockProducer_fk_purchasingDataID ON StockProducer');
        $this->addSql('DROP INDEX StockProducer_fk_glAccountID ON StockProducer');
        $this->addSql('DROP INDEX StockProducer_fk_parentID ON StockProducer');
        $this->addSql('DROP INDEX StockProducer_fk_customizationID ON StockProducer');

        $this->addSql('
            ALTER TABLE PurchOrders
            ADD locationID VARCHAR(5) DEFAULT NULL AFTER SupplierNo,
            DROP AllowPrint,
            CHANGE SupplierNo SupplierNo BIGINT UNSIGNED DEFAULT NULL,
            ADD producerID bigint unsigned default null
        ');

        $this->addSql('
            ALTER TABLE StockProducer
            CHANGE type type VARCHAR(10) NOT NULL,
            CHANGE version version VARCHAR(31) NOT NULL,
            CHANGE description description VARCHAR(100) NOT NULL,
            CHANGE dateUpdated dateUpdated DATETIME NOT NULL,
            CHANGE qtyOrdered qtyOrdered NUMERIC(16, 4) NOT NULL,
            CHANGE qtyIssued qtyIssued NUMERIC(16, 4) DEFAULT \'0\',
            CHANGE qtyReceived qtyReceived NUMERIC(16, 4) DEFAULT \'0\' NOT NULL,
            CHANGE qtyInvoiced qtyInvoiced NUMERIC(16, 4) DEFAULT \'0\' NOT NULL,
            CHANGE rework rework TINYINT(1) DEFAULT \'0\'
        ');

        $this->addSql("
            insert into PurchOrders (
             locationID
            , OrdDate
            , DatePrinted
            , Owner
            , Initiator
            , IntoStockLocation
            , ApprovalStatus
            , deliveryAddressId
            , producerID)
            select
             p.locationID
            , p.dateCreated
            , if(p.qtyIssued > 0, p.dateUpdated, null)
            , 'gordon'
            , 'WOSystem'
            , loc.LocCode
            , 'approved'
            , loc.addressId
            , p.id
            from StockProducer p, Locations loc
            where p.purchaseOrderID is NULL
            and loc.LocCode = '7'
        ");

        $this->addSql("
            update StockProducer p
            join PurchOrders po on p.id = po.producerID
            set p.purchaseOrderID = po.OrderNo
            where p.purchaseOrderID is null
        ");

        $this->addSql("
            update PurchOrders po
            join Locations l on po.SupplierNo = l.SupplierID
            set po.locationID = l.LocCode
            where po.locationID is null
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
