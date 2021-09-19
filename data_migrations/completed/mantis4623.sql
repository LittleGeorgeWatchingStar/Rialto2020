drop table if exists StockLevelStatus;
create table StockLevelStatus
  CHARACTER SET utf8 COLLATE utf8_unicode_ci
  select l.StockID as stockCode
    , LocCode as locationID
    , Quantity as qtyInStock
    , 0 as netQtyAllocated
    , ReorderLevel as orderPoint
    , null as dateUpdated
  from LocStock l
  join StockMaster i on l.StockID = i.StockID
  where i.MBflag in ('B', 'M');


ALTER TABLE StockLevelStatus CHANGE stockCode stockCode VARCHAR(20) NOT NULL, CHANGE locationID locationID VARCHAR(5) NOT NULL, CHANGE qtyInStock qtyInStock NUMERIC(12, 2) NOT NULL, CHANGE netQtyAllocated netQtyAllocated INT UNSIGNED NOT NULL, CHANGE orderPoint orderPoint INT UNSIGNED NOT NULL, CHANGE dateUpdated dateUpdated DATETIME DEFAULT NULL, ADD PRIMARY KEY (stockCode, locationID);
ALTER TABLE StockLevelStatus ADD CONSTRAINT FK_C0EC7AC1EC2233CA FOREIGN KEY (stockCode) REFERENCES StockMaster (StockID);
ALTER TABLE StockLevelStatus ADD CONSTRAINT FK_C0EC7AC1ADB908A5 FOREIGN KEY (locationID) REFERENCES Locations (LocCode);
CREATE INDEX IDX_C0EC7AC1EC2233CA ON StockLevelStatus (stockCode);
CREATE INDEX IDX_C0EC7AC1ADB908A5 ON StockLevelStatus (locationID);

rename table LocStock to erp_archive.LocStock;
