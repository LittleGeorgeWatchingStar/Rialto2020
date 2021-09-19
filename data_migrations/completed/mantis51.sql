select * from PurchData where SupplierNo not in (
select SupplierID from Suppliers )\G

select * from PurchData where StockID not in (
select StockID from StockMaster )\G

delete from PurchData where SupplierNo not in (
select SupplierID from Suppliers )
and StockID = 'CON144M';

insert into StockMaster (StockID, MBflag, CategoryID) values
('ICM064', 'B', 1),
('ICP021', 'B', 1);

update PurchData set StockID = 'GS400G01' where StockID = 'GS400G-01';

update PurchData set CatalogNo = SupplierDescription
where (CatalogNo = '' or CatalogNo = 'NOCAT') and SupplierDescription != '';

update PurchData set CatalogNo = ManufacturerCode
where (CatalogNo = '' or CatalogNo = 'NOCAT') and ManufacturerCode != '';

select SupplierNo, CatalogNo, group_concat(StockID), count(SupplierNo)
from PurchData
group by SupplierNo, CatalogNo
having count(SupplierNo) > 1;

update PurchData set CatalogNo = StockID
where (CatalogNo = '');

create temporary table TempPD
select CatalogNo
from PurchData
group by SupplierNo, CatalogNo
having count(SupplierNo) > 1;

update PurchData set CatalogNo = concat(CatalogNo, ' ', StockID)
where CatalogNo in ( select CatalogNo from TempPD );

select SupplierNo, CatalogNo, group_concat(StockID), count(SupplierNo)
from PurchData
group by SupplierNo, CatalogNo
having count(SupplierNo) > 1;

alter table PurchData
drop primary key,
add column ID serial first,
modify column CatalogNo varchar(50) not null default '' after SupplierNo,
add column QuotationNo varchar(50) not null default '' after CatalogNo,
modify column ManufacturerCode varchar(50) not null default '',
modify column BinStyle varchar(32) not null default '',
modify column Turnkey boolean not null default 0,
modify column BinSize int unsigned not null default 0,
add column Version varchar(31) not null default '-any-' after StockID;

alter table PurchData
add primary key (ID),
add unique key SupplierNo_CatalogNo_QuotationNo (SupplierNo, CatalogNo, QuotationNo),
add constraint PurchData_fk_SupplierNo
foreign key (SupplierNo) references Suppliers (SupplierID)
on update cascade on delete cascade,
add constraint PurchData_fk_StockID
foreign key (StockID) references StockMaster (StockID)
on update cascade on delete cascade;

drop table if exists PurchasingCost;

create table PurchasingCost (
    id serial,
    purchasingDataId bigint unsigned not null default 0,
    minimumOrderQty int unsigned not null default 0,
    leadTime smallint unsigned not null default 0,
    binSize int unsigned not null default 0,
    cost decimal (16,4) not null default 0.0,
    labourCost decimal (16,4) not null default 0.0,
    primary key (id),
    unique key purchasingDataId_minimumOrderQty_leadTime (purchasingDataId, minimumOrderQty, leadTime),
    constraint PurchasingCost_fk_purchasingDataId
    foreign key (purchasingDataId) references PurchData (ID)
    on delete cascade
);

-- all suppliers except Digikey
insert into PurchasingCost
(purchasingDataId, minimumOrderQty, leadTime, binSize, cost, labourCost)
select ID, 0, LeadTime, 1, Price, Labourcost
from PurchData where SupplierNo != 3;

insert into PurchasingCost
(purchasingDataId, minimumOrderQty, leadTime, binSize, cost, labourCost)
select ID, BinSize, LeadTime, BinSize, Price, Labourcost
from PurchData where SupplierNo != 3 and BinSize > 1;

-- Digikey
insert into PurchasingCost
(purchasingDataId, minimumOrderQty, leadTime, binSize, cost, labourCost)
select ID, 0, LeadTime, 1, Price, Labourcost
from PurchData where SupplierNo = 3 and CatalogNo like '%-1-ND';

insert into PurchasingCost
(purchasingDataId, minimumOrderQty, leadTime, binSize, cost, labourCost)
select ID, BinSize, LeadTime, BinSize, Price, Labourcost
from PurchData where SupplierNo = 3 and CatalogNo like '%-2-ND' and BinSize > 1;

insert into PurchasingCost
(purchasingDataId, minimumOrderQty, leadTime, binSize, cost, labourCost)
select p.ID, s.EOQ, p.LeadTime, 1, p.Price, p.Labourcost
from PurchData p join StockMaster s on p.StockID = s.StockID
where p.SupplierNo = 3 and p.CatalogNo like '%-6-ND';

insert into PurchasingCost
(purchasingDataId, minimumOrderQty, leadTime, binSize, cost, labourCost)
select p.ID, 0, p.LeadTime, 1, p.Price, p.Labourcost
from PurchData p left join PurchasingCost c on p.ID = c.purchasingDataId
where c.id is null;

alter table PurchOrderDetails
add column PurchDataID bigint unsigned null default null after OrderNo,
add column CatalogNo varchar(50) not null default '' after PurchDataID,
add column QuotationNo varchar(50) not null default '' after CatalogNo,
add column ManufacturerCode varchar(50) not null default '' after QuotationNo,
modify column ItemCode varchar(20) null default null,
add constraint PurchOrderDetails_fk_PurchDataID
foreign key (PurchDataID) references PurchData (ID)
on update restrict on delete restrict;

update PurchOrderDetails
set ItemCode = null where ItemCode = '';

alter table PurchOrderDetails
add constraint PurchOrderDetails_fk_ItemCode
foreign key (ItemCode) references StockMaster (StockID)
on update cascade on delete restrict;

update PurchOrderDetails item
join PurchOrders ord on item.OrderNo = ord.OrderNo
join PurchData dat on item.ItemCode = dat.StockID
set item.PurchDataID = dat.ID,
item.CatalogNo = dat.CatalogNo,
item.QuotationNo = dat.QuotationNo,
item.ManufacturerCode = dat.ManufacturerCode
where ord.SupplierNo = dat.SupplierNo;