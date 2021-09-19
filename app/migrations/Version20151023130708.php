<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20151023130708 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE PurchOrders DROP producerID');
        $this->addSql('ALTER TABLE StockProducer DROP workOrderID, DROP poItemID, DROP locationID, DROP stockCode, CHANGE purchaseOrderID purchaseOrderID BIGINT UNSIGNED NOT NULL');

        $this->addSql('ALTER TABLE PurchOrders ADD CONSTRAINT FK_687035E648CBED4C FOREIGN KEY (SupplierNo) REFERENCES Suppliers (SupplierID) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE PurchOrders ADD CONSTRAINT FK_687035E6ADB908A5 FOREIGN KEY (locationID) REFERENCES Locations (LocCode) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE PurchOrders ADD CONSTRAINT FK_687035E6EA1C978 FOREIGN KEY (Owner) REFERENCES WWW_Users (UserID) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE PurchOrders ADD CONSTRAINT FK_687035E64981FCE1 FOREIGN KEY (ShipperID) REFERENCES Shippers (Shipper_ID) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE PurchOrders ADD CONSTRAINT FK_687035E6CCB9F89C FOREIGN KEY (IntoStockLocation) REFERENCES Locations (LocCode) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE PurchOrders ADD CONSTRAINT FK_687035E61878BDD0 FOREIGN KEY (deliveryAddressId) REFERENCES Geography_Address (id) ON DELETE RESTRICT');

        $this->addSql('ALTER TABLE StockProducer ADD CONSTRAINT FK_B08291A12CAC88B5 FOREIGN KEY (purchaseOrderID) REFERENCES PurchOrders (OrderNo)');
        $this->addSql('ALTER TABLE StockProducer ADD CONSTRAINT FK_B08291A1AA2D3605 FOREIGN KEY (purchasingDataID) REFERENCES PurchData (ID)');
        $this->addSql('ALTER TABLE StockProducer ADD CONSTRAINT FK_B08291A1E4FBD384 FOREIGN KEY (glAccountID) REFERENCES ChartMaster (AccountCode)');
        $this->addSql('ALTER TABLE StockProducer ADD CONSTRAINT FK_B08291A12B806C26 FOREIGN KEY (parentID) REFERENCES StockProducer (id)');
        $this->addSql('ALTER TABLE StockProducer ADD CONSTRAINT FK_B08291A1B81D610D FOREIGN KEY (customizationID) REFERENCES Customization (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
