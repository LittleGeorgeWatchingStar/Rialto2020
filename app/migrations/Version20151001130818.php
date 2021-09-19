<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Drop address columns from PurchOrders and recreate foreign keys.
 */
class Version20151001130818 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE PurchOrders DROP Addr1, DROP Addr2, DROP MailStop, DROP City, DROP State, DROP Zip, DROP Country');
        $this->addSql('ALTER TABLE PurchOrders ADD CONSTRAINT FK_687035E648CBED4C FOREIGN KEY (SupplierNo) REFERENCES Suppliers (SupplierID) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE PurchOrders ADD CONSTRAINT FK_687035E6EA1C978 FOREIGN KEY (Owner) REFERENCES WWW_Users (UserID) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE PurchOrders ADD CONSTRAINT FK_687035E64981FCE1 FOREIGN KEY (ShipperID) REFERENCES Shippers (Shipper_ID) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE PurchOrders ADD CONSTRAINT FK_687035E6CCB9F89C FOREIGN KEY (IntoStockLocation) REFERENCES Locations (LocCode) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE PurchOrders ADD CONSTRAINT FK_687035E61878BDD0 FOREIGN KEY (deliveryAddressId) REFERENCES Geography_Address (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE PurchOrders CHANGE deliveryAddressId deliveryAddressId BIGINT UNSIGNED NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
