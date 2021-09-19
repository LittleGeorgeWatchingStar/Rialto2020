-- find stock item producers that have no purchasing data
select distinct po.SupplierNo
  , sp.purchaseOrderID
  , sp.stockCode
  , sp.version
  , substring(sp.description, 1, 20) as description
  , sp.expectedUnitCost
from StockProducer sp
  left join PurchOrders po on sp.purchaseOrderID = po.OrderNo
where sp.purchasingDataID is null
and sp.stockCode is not null;


-- try to backfill from existing purchdata records
-- labour:
update StockProducer sp
  join PurchData pd
    on pd.LocCode = sp.locationID
       and pd.StockID = sp.stockCode
       and pd.Version in (sp.version, '-any-')
set sp.purchasingDataID = pd.ID
where sp.purchasingDataID is null
      and sp.stockCode is not null
      and sp.description like 'Labour%';

-- parts:
update StockProducer sp
  join PurchOrders po
    on sp.purchaseOrderID = po.OrderNo
  join PurchData pd
    on pd.SupplierNo = po.SupplierNo
       and pd.StockID = sp.stockCode
       and pd.Version in (sp.version, '-any-')
set sp.purchasingDataID = pd.ID
where sp.purchasingDataID is null
      and sp.stockCode is not null;



-- fix old builds from suppliers
insert into PurchData
(SupplierNo, CatalogNo, StockID, Version, BinStyle, endOfLife)
  select distinct po.SupplierNo
    , concat(sp.stockCode, if(sp.version = '', '', concat('-R', sp.version)))
    , sp.stockCode
    , sp.version
    , 'bin'
    , curdate() - interval 1 day
  from StockProducer sp
    join PurchOrders po
      on sp.purchaseOrderID = po.OrderNo
    left join PurchData pd
      on pd.SupplierNo = po.SupplierNo
         and pd.StockID = sp.stockCode
         and pd.Version in (sp.version, '-any-')
  where sp.purchasingDataID is null
        and sp.stockCode is not null
        and sp.description like 'Labour%'
        and pd.ID is null;

update StockProducer sp
  join PurchOrders po
    on sp.purchaseOrderID = po.OrderNo
  join PurchData pd
    on pd.SupplierNo = po.SupplierNo
       and pd.StockID = sp.stockCode
       and pd.Version in (sp.version, '-any-')
set sp.purchasingDataID = pd.ID
where sp.purchasingDataID is null
      and sp.stockCode is not null
      and sp.description like 'Labour%';



-- fix old builds at HQ
insert into PurchData
(LocCode, CatalogNo, StockID, Version, BinStyle, endOfLife)
  select distinct sp.locationID
    , concat(sp.stockCode, if(sp.version = '', '', concat('-R', sp.version)))
    , sp.stockCode
    , sp.version
    , 'bin'
    , curdate() - interval 1 day
  from StockProducer sp
    left join PurchData pd
      on pd.LocCode = sp.locationID
         and pd.StockID = sp.stockCode
         and pd.Version in (sp.version, '-any-')
  where sp.purchasingDataID is null
        and sp.locationID is not null
        and sp.stockCode is not null
        and sp.description like 'Labour%'
        and pd.ID is null;

update StockProducer sp
  join PurchData pd
    on pd.LocCode = sp.locationID
       and pd.StockID = sp.stockCode
       and pd.Version in (sp.version, '-any-')
set sp.purchasingDataID = pd.ID
where sp.purchasingDataID is null
      and sp.stockCode is not null
      and sp.description like 'Labour%';



-- physical parts
insert into PurchData
(SupplierNo, CatalogNo, StockID, Version, BinStyle, endOfLife)
  select distinct po.SupplierNo
    , concat(sp.stockCode, if(sp.version = '', '', concat('-R', sp.version)))
    , sp.stockCode
    , sp.version
    , 'bin'
    , curdate() - interval 1 day
  from StockProducer sp
    join PurchOrders po on sp.purchaseOrderID = po.OrderNo
  where sp.purchasingDataID is null
        and sp.stockCode is not null;


update StockProducer sp
  join PurchOrders po
    on sp.purchaseOrderID = po.OrderNo
  join PurchData pd
    on pd.SupplierNo = po.SupplierNo
       and pd.StockID = sp.stockCode
       and pd.Version in (sp.version, '-any-')
set sp.purchasingDataID = pd.ID
where sp.purchasingDataID is null
      and sp.stockCode is not null;


-- populate cost breaks for new purchdata records
insert into PurchasingCost
(purchasingDataId, minimumOrderQty, unitCost, manufacturerLeadTime)
select pd.ID, 1, max(sp.expectedUnitCost), 7
from StockProducer sp
  join PurchData pd
    on sp.purchasingDataId = pd.ID
  left join PurchasingCost pc
    on pc.purchasingDataId = pd.ID
where pc.id is null
group by pd.ID;


-- tidy up
ALTER TABLE PurchData DROP FOREIGN KEY PurchData_fk_BinStyle;
ALTER TABLE PurchData DROP FOREIGN KEY PurchData_fk_LocCode;
ALTER TABLE PurchData DROP FOREIGN KEY PurchData_fk_StockID;
DROP INDEX ID ON PurchData;
DROP INDEX Preferred ON PurchData;
DROP INDEX ID_2 ON PurchData;
ALTER TABLE PurchData ADD CONSTRAINT FK_BBC917399B8EF6 FOREIGN KEY (StockID) REFERENCES StockMaster (StockID);
ALTER TABLE PurchData ADD CONSTRAINT FK_BBC917CF9430DB FOREIGN KEY (LocCode) REFERENCES Locations (LocCode);
ALTER TABLE PurchData ADD CONSTRAINT FK_BBC91716141D96 FOREIGN KEY (BinStyle) REFERENCES BinStyle (`name`);
