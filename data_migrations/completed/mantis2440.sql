CREATE TABLE WorkType (id VARCHAR(20) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE Requirement ADD workTypeID VARCHAR(20) DEFAULT NULL, DROP unitStandardCost, CHANGE consumerType consumerType VARCHAR(20) NOT NULL;
ALTER TABLE Substitutions ADD workTypeID VARCHAR(20) DEFAULT NULL, DROP Action, DROP WorkCenter, CHANGE dnpDesignators dnpDesignators VARCHAR(255) NOT NULL, CHANGE addDesignators addDesignators VARCHAR(255) NOT NULL, CHANGE Instructions Instructions VARCHAR(255) NOT NULL, CHANGE PriceChange PriceChange NUMERIC(12, 2) NOT NULL;
ALTER TABLE BOM ADD workTypeID VARCHAR(20) DEFAULT NULL, DROP WorkCentreAdded, DROP LocCode, DROP EffectiveAfter, DROP EffectiveTo, CHANGE Parent Parent VARCHAR(20) NOT NULL, CHANGE Component Component VARCHAR(20) NOT NULL, CHANGE Quantity Quantity NUMERIC(12, 4) NOT NULL, CHANGE ParentVersion ParentVersion VARCHAR(31) NOT NULL, CHANGE ComponentVersion ComponentVersion VARCHAR(31) NOT NULL, CHANGE Designators Designators VARCHAR(1000) NOT NULL;




insert into WorkType
(id, name) VALUES
  ('smt', 'Surface mount'),
  ('package', 'Manual package'),
  ('flash', 'Flash memory'),
  ('print', 'Print'),
  ('rework', 'Rework');

update BOM as b set b.workTypeID = 'smt' where b.Parent like 'BRD%';
update BOM as b set b.workTypeID = 'smt' where b.Parent like 'GS%';
update BOM as b set b.workTypeID = 'smt' where b.Parent like 'MOD%';

update BOM as b set b.workTypeID = 'package' where b.Parent like 'BRD%' and Component like 'BAG%';
update BOM as b set b.workTypeID = 'package' where b.Parent like 'GS%' and Component like 'BAG%';

update BOM as b set b.workTypeID = 'package' where b.Parent like 'PKG%';
update BOM as b set b.workTypeID = 'package' where b.Parent like 'GUM%';
update BOM as b set b.workTypeID = 'package' where b.Parent like 'ASM%';
update BOM as b set b.workTypeID = 'package' where b.Parent like 'KIT%';
update BOM as b set b.workTypeID = 'package' where b.Parent like 'CAS%';
update BOM as b set b.workTypeID = 'package' where b.Parent like 'WS%';
update BOM as b set b.workTypeID = 'package' where b.Parent like 'NS%';
update BOM as b set b.workTypeID = 'package' where b.Parent like 'SDP%';
update BOM as b set b.workTypeID = 'package' where b.Parent like 'TS400XM%';

update BOM as b set b.workTypeID = 'package' where b.Parent like 'CONH0030%';

update BOM as b set b.workTypeID = 'flash' where b.Parent like 'USD%';
update BOM as b set b.workTypeID = 'flash' where b.Parent like 'ICM%';
update BOM as b set b.workTypeID = 'flash' where b.Parent like 'ICG%';
update BOM as b set b.workTypeID = 'flash' where b.Parent like 'ICP%';

update BOM as b set b.workTypeID = 'print' where b.Parent like 'LBL%';

update Substitutions set workTypeID = 'smt' where SubstituteID is not null;

update Requirement req
  join StockProducer wo on req.consumerID = wo.id
set req.workTypeID = 'rework'
where wo.rework = 1;


update Requirement req
  join StockProducer wo on req.consumerID = wo.id
  join PurchData pd on wo.purchasingDataID = pd.ID
  join BOM bom
    on bom.Parent = pd.StockID
       and bom.ParentVersion = wo.version
       and bom.Component = req.stockCode
set req.workTypeID = bom.workTypeID
where req.consumerType = 'WorkOrder'
      and req.workTypeID is null;


update Requirement req
  join StockProducer wo on req.consumerID = wo.id
  join PurchData pd on wo.purchasingDataID = pd.ID
  join BOM bom
    on bom.Parent = pd.StockID
       and bom.Component = req.stockCode
set req.workTypeID = bom.workTypeID
where consumerType = 'WorkOrder'
      and req.workTypeID is null;

update Requirement set workTypeID = 'package' where consumerType = 'WorkOrder' and stockCode like 'BAG%' and workTypeID is null;
update Requirement set workTypeID = 'package' where consumerType = 'WorkOrder' and stockCode like 'BOX%' and workTypeID is null;
update Requirement set workTypeID = 'package' where consumerType = 'WorkOrder' and stockCode like 'LBL%' and workTypeID is null;
update Requirement set workTypeID = 'package' where consumerType = 'WorkOrder' and stockCode like 'GUM%' and workTypeID is null;
update Requirement set workTypeID = 'package' where consumerType = 'WorkOrder' and stockCode like 'PKG%' and workTypeID is null;
update Requirement set workTypeID = 'flash'   where consumerType = 'WorkOrder' and stockCode like 'MMC%' and workTypeID is null;
update Requirement set workTypeID = 'smt'     where consumerType = 'WorkOrder' and workTypeID is null;

select count(*) from BOM where workTypeID is null;
select count(*) from Requirement where consumerType = 'WorkOrder' and workTypeID is null;

select DISTINCT wo.id, wo.locationID, pd.StockID as parent, wo.version
  , req.stockCode
  , bom.Parent, bom.Component
from Requirement req
  left join StockProducer wo on req.consumerID = wo.id
  left join PurchData pd on wo.purchasingDataID = pd.ID
  left join BOM bom
    on bom.Parent = pd.StockID
       and bom.Component = req.stockCode
where consumerType = 'WorkOrder'
      and req.workTypeID is null;




ALTER TABLE Requirement ADD CONSTRAINT FK_5DA3DA875F526DD FOREIGN KEY (workTypeID) REFERENCES WorkType (id);
CREATE INDEX IDX_5DA3DA875F526DD ON Requirement (workTypeID);
ALTER TABLE Substitutions ADD CONSTRAINT FK_8BDAF8C5F526DD FOREIGN KEY (workTypeID) REFERENCES WorkType (id);
CREATE INDEX IDX_8BDAF8C5F526DD ON Substitutions (workTypeID);
ALTER TABLE BOM MODIFY workTypeID VARCHAR(20) NOT NULL;
ALTER TABLE BOM ADD CONSTRAINT FK_F3D3EE5B5F526DD FOREIGN KEY (workTypeID) REFERENCES WorkType (id);
CREATE INDEX IDX_F3D3EE5B5F526DD ON BOM (workTypeID);




update BOM b
join ItemVersion v
  on b.Parent = v.stockCode and b.ParentVersion = v.version
set b.Component = 'LBL0003', b.ComponentVersion = '-auto-'
where b.Component = 'LBL0002'
and v.active = 1;

update ItemVersion v
  set v.version = '-auto-',
    v.active = 1
where v.stockCode = 'LBL0003';

update BOM b
  set b.ParentVersion = '-auto-'
where b.Parent = 'LBL0003';

update StockMaster
  set ShippingVersion = '-auto-',
    AutoBuildVersion = '-auto-',
    Discontinued = 0
where StockID = 'LBL0003';

update PurchData
    set Version = '-auto-'
  , Preferred = 1
  , endOfLife = null
where StockID = 'LBL0003'
and LocCode = '7';

update PurchData
set Preferred = 0
where StockID = 'LBL0003'
and LocCode != '7';
