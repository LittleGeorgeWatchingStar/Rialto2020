alter table PurchData
modify column SupplierNo bigint unsigned null default null,
add column LocCode varchar(5) null default null after SupplierNo,
drop key SupplierNo_CatalogNo_QuotationNo,
add constraint SupplierNo_LocCode_CatalogNo_QuotationNo
unique key (SupplierNo, LocCode, CatalogNo, QuotationNo),
add constraint PurchData_fk_LocCode
foreign key (LocCode) references Locations (LocCode) on update cascade;

alter table PurchData
add key (ID, StockID, LocCode);

update PurchData pd
join Locations l
on pd.SupplierNo = l.SupplierID
set pd.LocCode = l.LocCode;

update Locations set SupplierID = null
where SupplierID = 217
and LocCode in (7,14);

alter table Locations
add unique key (SupplierID);

update PurchData pd
join Locations l
on pd.LocCode = l.LocCode
set pd.SupplierNo = l.SupplierID;

alter table WorksOrders
add column PurchDataID bigint unsigned null default null after WORef;

alter table WorksOrders
add constraint WorksOrders_fk_PurchDataID
foreign key (PurchDataID, StockID, LocCode)
references PurchData (ID, StockID, LocCode)
on delete restrict on update restrict;

select wo.WORef, wo.StockID, wo.LocCode,
count(pd.ID) as num
from WorksOrders wo
join PurchData pd on wo.StockID = pd.StockID and wo.LocCode = pd.LocCode
where wo.PurchDataID is null
group by wo.WORef
having num > 1;

update WorksOrders wo
join PurchData pd on wo.StockID = pd.StockID and wo.LocCode = pd.LocCode
set wo.PurchDataID = pd.ID
where wo.PurchDataID is null;

update PurchasingCost
set cost = labourCost
where cost = 0
and labourCost > 0;