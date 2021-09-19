<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add deliveryAddressId to PurchOrders.
 */
class Version20151001102954 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');


        $this->addSql("update PurchOrders set Initiator = '' where Initiator is null");
        $this->addSql("update PurchOrders set OrdDate = DatePrinted where OrdDate is null and DatePrinted is not null");
        $this->addSql("set @min_ord_date = (select min(OrdDate) from PurchOrders)");
        $this->addSql("update PurchOrders set OrdDate = @min_ord_date where OrdDate is null");


        $this->addSql('DROP INDEX OrderNo ON PurchOrders');
        $this->addSql('DROP INDEX OrdDate ON PurchOrders');
        $this->addSql('DROP INDEX AllowPrintPO ON PurchOrders');
        $this->addSql('ALTER TABLE PurchOrders DROP FOREIGN KEY PurchOrders_fk_Owner');
        $this->addSql('ALTER TABLE PurchOrders DROP FOREIGN KEY PurchOrders_fk_ShipperID');
        $this->addSql('ALTER TABLE PurchOrders DROP FOREIGN KEY PurchOrders_fk_SupplierNo');
        $this->addSql('DROP INDEX SupplierNo ON PurchOrders');
        $this->addSql('DROP INDEX PurchOrders_fk_Owner ON PurchOrders');
        $this->addSql('DROP INDEX PurchOrders_fk_ShipperID ON PurchOrders');
        $this->addSql('DROP INDEX IntoStockLocation ON PurchOrders');


        $this->addSql('ALTER TABLE PurchOrders ADD deliveryAddressId BIGINT UNSIGNED DEFAULT NULL, DROP RequisitionNo, CHANGE SupplierNo SupplierNo BIGINT UNSIGNED NOT NULL, CHANGE OrdDate OrdDate DATETIME NOT NULL, CHANGE Initiator Initiator VARCHAR(10) NOT NULL, CHANGE IntoStockLocation IntoStockLocation VARCHAR(5) NOT NULL');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
