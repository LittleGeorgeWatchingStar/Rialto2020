<?php

namespace Rialto\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Allow Transfers to be stock locations.
 */
class Version20161117110531 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        // expand
        $this->addSql('ALTER TABLE StockSerialItems ADD transferId BIGINT UNSIGNED DEFAULT NULL AFTER LocCode, CHANGE LocCode LocCode VARCHAR(5) DEFAULT NULL');
        $this->addSql('ALTER TABLE StockMove ADD transferId BIGINT UNSIGNED DEFAULT NULL AFTER locationID, CHANGE locationID locationID VARCHAR(5) DEFAULT NULL');

        $this->addSql('ALTER TABLE LocTransferHeader DROP FOREIGN KEY FK_B9E48C13AA6A11A1');
        $this->addSql('DROP INDEX FK_B9E48C13AA6A11A1 ON LocTransferHeader');

        // data
        $this->addSql("
            UPDATE StockMove sm
            JOIN LocTransferHeader tr ON sm.systemTypeNumber = tr.ID
            SET sm.locationID = NULL, sm.transferId = tr.ID
            WHERE sm.systemTypeID = 16
            AND sm.locationID = 'TRANS'
        ");
        $this->addSql("
            UPDATE StockMove sm
            JOIN LocTransfers tri ON tri.SerialNo = sm.binID
            JOIN LocTransferHeader tr ON tri.Reference = tr.ID
            SET sm.locationID = NULL, sm.transferId = tr.ID
            WHERE sm.locationID = 'TRANS' 
            AND tr.DateShipped <= sm.dateMoved
            AND tri.RecDate >= sm.dateMoved
        ");
        $this->addSql("
            UPDATE StockMove sm
            JOIN LocTransfers tri ON tri.SerialNo = sm.binID
            JOIN LocTransferHeader tr ON tri.Reference = tr.ID
            SET sm.locationID = NULL, sm.transferId = tr.ID
            WHERE sm.locationID = 'TRANS' 
            AND tr.DateShipped <= sm.dateMoved
            AND tri.RecDate IS NULL;
        ");
        $this->addSql("
            UPDATE StockMove
            SET locationID = '7'
            WHERE binID = 16287
            AND locationID = 'TRANS'
            AND id = 381773
        ");
        $this->addSql("
            UPDATE StockMove
            SET locationID = NULL, transferId = 26964
            WHERE binID = 18058
            AND locationID = 'TRANS'
            AND id = 333572
        ");
        $this->addSql("
            UPDATE StockMove
            SET locationID = NULL, transferId = 27043
            WHERE binID = 18343
            AND locationID = 'TRANS'
            AND id = 337644
        ");
        $this->addSql("
            UPDATE StockMove
            SET locationID = NULL
            WHERE systemTypeID = 17
            AND locationID = 'TRANS'
        ");

        $this->addSql("
            update StockSerialItems bin 
            join StockMove sm on sm.binID = bin.SerialNo
            set bin.LocCode = NULL, bin.transferId = sm.transferId
            where bin.LocCode = 'TRANS'
            and sm.transferId is not null
            and not exists (
                select 1
                from StockMove newer
                where newer.binID = bin.SerialNo
                and newer.dateMoved > sm.dateMoved
            )
        ");

        $this->addSql("
            update StockSerialItems bin 
            join StockMove sm on sm.binID = bin.SerialNo
            set bin.LocCode = NULL
            where bin.LocCode = 'TRANS'
            and sm.transferId is null
            and sm.locationID is null
            and not exists (
                select 1
                from StockMove newer
                where newer.binID = bin.SerialNo
                and newer.dateMoved > sm.dateMoved
            )
        ");
        $this->addSql("
            update StockSerialItems
            set LocCode = null, transferId = 26983
            where StockID = 'BOX007'
            and SerialNo = 13938
            and LocCode = 'TRANS'
        ");

        $this->addSql("delete from StockLevelStatus where locationID = 'TRANS'");
        $this->addSql("delete from Locations where LocCode = 'TRANS'");


        // contract
        $this->addSql('ALTER TABLE StockSerialItems ADD CONSTRAINT FK_C2D350B3AE9826DA FOREIGN KEY (transferId) REFERENCES LocTransferHeader (ID) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE StockSerialItems ADD CONSTRAINT FK_C2D350B3837341C5 FOREIGN KEY (customizationId) REFERENCES Customization (id) ON DELETE RESTRICT');
        $this->addSql('ALTER TABLE LocTransferHeader DROP inTransitID');
        $this->addSql('ALTER TABLE StockMove ADD CONSTRAINT FK_F6F8B689AE9826DA FOREIGN KEY (transferId) REFERENCES LocTransferHeader (ID) ON DELETE RESTRICT');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->throwIrreversibleMigrationException();
    }
}
