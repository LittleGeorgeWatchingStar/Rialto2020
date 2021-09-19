ALTER TABLE LocTransfers DROP FOREIGN KEY LocTransfers_fk_Reference;
ALTER TABLE LocTransfers MODIFY StockID VARCHAR(20) NULL;
DELETE from LocTransfers where ID in (5881, 6027, 6177);

ALTER TABLE LocTransferHeader DROP FOREIGN KEY LocTransferHeader_fk_inTransitID;
ALTER TABLE LocTransferHeader DROP FOREIGN KEY LocTransferHeader_ibfk_1;
ALTER TABLE LocTransferHeader DROP FOREIGN KEY LocTransferHeader_ibfk_2;
ALTER TABLE LocTransferHeader CHANGE ID ID BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, CHANGE FromLocation FromLocation VARCHAR(5) NOT NULL, CHANGE ToLocation ToLocation VARCHAR(5) NOT NULL, CHANGE DateShipped DateShipped DATETIME DEFAULT NULL;
ALTER TABLE LocTransferHeader ADD CONSTRAINT FK_B9E48C134DCF32B7 FOREIGN KEY (FromLocation) REFERENCES Locations (LocCode);
ALTER TABLE LocTransferHeader ADD CONSTRAINT FK_B9E48C13AA6A11A1 FOREIGN KEY (inTransitID) REFERENCES Locations (LocCode);
ALTER TABLE LocTransferHeader ADD CONSTRAINT FK_B9E48C13126534C5 FOREIGN KEY (ToLocation) REFERENCES Locations (LocCode);
DROP INDEX ID ON LocTransfers;
DROP INDEX Reference_StockID_SerialNo ON LocTransfers;
DROP INDEX ShipLoc ON LocTransfers;
DROP INDEX RecLoc ON LocTransfers;
DROP INDEX StockID ON LocTransfers;
ALTER TABLE LocTransfers DROP ShipLoc, DROP RecLoc, CHANGE Reference Reference BIGINT UNSIGNED NOT NULL, CHANGE ShipQty ShipQty INT NOT NULL, CHANGE RecQty RecQty INT NOT NULL;
CREATE UNIQUE INDEX UNIQ_53924E862C52CBB0C46D4D98 ON LocTransfers (Reference, SerialNo);


CREATE TABLE TransferOrder (transferID BIGINT UNSIGNED NOT NULL, purchaseOrderID BIGINT UNSIGNED NOT NULL, INDEX IDX_E246AB1095F60612 (transferID), INDEX IDX_E246AB102CAC88B5 (purchaseOrderID), PRIMARY KEY(transferID, purchaseOrderID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE TransferOrder ADD CONSTRAINT FK_E246AB1095F60612 FOREIGN KEY (transferID) REFERENCES LocTransferHeader (ID);
ALTER TABLE TransferOrder ADD CONSTRAINT FK_E246AB102CAC88B5 FOREIGN KEY (purchaseOrderID) REFERENCES PurchOrders (OrderNo);

insert into TransferOrder
select distinct t.ID, po.OrderNo
from LocTransferHeader t
join TransferWorkOrder two on two.transferID = t.ID
join StockProducer wo on two.workOrderID = wo.id
join PurchOrders po on wo.purchaseOrderID = po.OrderNo;

DROP VIEW TransferWorkOrder;

ALTER TABLE LocTransfers ADD CONSTRAINT FK_53924E862C52CBB0 FOREIGN KEY (Reference) REFERENCES LocTransferHeader (ID);
