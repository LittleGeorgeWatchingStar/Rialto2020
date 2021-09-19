<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Clean up and add columns to LocTransfer*
 */
class Version20161101142958 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE TransferOrder DROP FOREIGN KEY FK_E246AB102CAC88B5');
        $this->addSql('ALTER TABLE TransferOrder DROP FOREIGN KEY FK_E246AB1095F60612');
        $this->addSql('ALTER TABLE TransferOrder ADD CONSTRAINT FK_E246AB102CAC88B5 FOREIGN KEY (purchaseOrderID) REFERENCES PurchOrders (OrderNo) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE TransferOrder ADD CONSTRAINT FK_E246AB1095F60612 FOREIGN KEY (transferID) REFERENCES LocTransferHeader (ID) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE LocTransferHeader ADD dateRequested DATETIME NULL AFTER ToLocation, ADD dateKitted DATETIME DEFAULT NULL AFTER dateRequested, ADD shipperId BIGINT UNSIGNED NULL');
        $this->addSql("UPDATE LocTransferHeader SET dateRequested = DateShipped, dateKitted = DateShipped, shipperId = 4"); // hand-carried
        $this->addSql("DELETE FROM LocTransferHeader WHERE dateRequested IS NULL");
        $this->addSql('ALTER TABLE LocTransferHeader MODIFY dateRequested DATETIME NOT NULL, MODIFY shipperId BIGINT UNSIGNED NOT NULL');
        $this->addSql('ALTER TABLE LocTransferHeader ADD CONSTRAINT FK_B9E48C13F01E5E8A FOREIGN KEY (shipperId) REFERENCES Shippers (Shipper_ID) ON DELETE RESTRICT');

        $this->addSql("
            CREATE TABLE Stock_UncontrolledTransferItem DEFAULT CHARACTER SET utf8
            SELECT ID, Reference, StockID, ShipQty, RecQty, ShipDate, RecDate 
            FROM LocTransfers
            WHERE SerialNo IS NULL
        ");
        $this->addSql("DELETE FROM LocTransfers WHERE SerialNo IS NULL");

        $this->addSql('ALTER TABLE LocTransfers DROP FOREIGN KEY FK_53924E862C52CBB0');
        $this->addSql('ALTER TABLE LocTransfers DROP FOREIGN KEY LocTransfers_fk_SerialNo');
        $this->addSql('DROP INDEX Reference ON LocTransfers');
        $this->addSql('DROP INDEX LocTransfers_fk_SerialNo ON LocTransfers');

        $this->addSql('ALTER TABLE LocTransfers DROP StockID, DROP ShipDate, CHANGE RecQty RecQty INT DEFAULT 0 NOT NULL, CHANGE SerialNo SerialNo BIGINT UNSIGNED NOT NULL AFTER Reference');

        $this->addSql('ALTER TABLE LocTransfers ADD CONSTRAINT FK_53924E862C52CBB0 FOREIGN KEY (Reference) REFERENCES LocTransferHeader (ID) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE LocTransfers ADD CONSTRAINT FK_53924E86C46D4D98 FOREIGN KEY (SerialNo) REFERENCES StockSerialItems (SerialNo) ON DELETE RESTRICT');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
