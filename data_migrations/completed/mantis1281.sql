-- convert dates to datetimes
alter table WorksOrders modify column ReleasedDate datetime not null default 0;

update WorksOrders set ReleasedDate = 0 where ReleasedDate < '1970-01-01' and ReleasedDate > 0;

alter table LocTransfers modify column RecDate datetime not null default 0;
alter table PurchOrders modify column DatePrinted datetime not null default 0;


-- kill bad uncontrolled allocations
select a.StockID, a.Qty, a.Delivered from StockAllocation a join StockMaster i on i.StockID = a.StockID where a.SourceType = 'StockLevel' and i.Controlled = 1;

delete a.* from StockAllocation a join StockMaster i on i.StockID = a.StockID where a.SourceType = 'StockLevel' and i.Controlled = 1;


-- add unique constraint to allocations
create table StockAllocationNew (
    AllocationID serial,
    ConsumerType varchar(30) not null default '',
    ConsumerNo bigint unsigned not null default 0,
    SourceType varchar(30) not null default '',
    SourceNo bigint unsigned not null default 0,
    StockID varchar(31) not null default '',
    Qty int not null default 0,
    Delivered int not null default 0,
    primary key (AllocationID),
    unique key (ConsumerType, ConsumerNo, SourceType, SourceNo, StockID),
    constraint StockAllocation_fk_StockID
    foreign key (StockID) references StockMaster (StockID)
    on update cascade on delete cascade
);


insert into StockAllocationNew
(ConsumerType, ConsumerNo, SourceType, SourceNo, StockID, Qty, Delivered)
select ConsumerType, ConsumerNo, SourceType, SourceNo, StockID, sum(Qty), sum(Delivered)
from StockAllocation group by ConsumerType, ConsumerNo, SourceType, SourceNo, StockID;

drop table StockAllocation;

rename table StockAllocationNew to StockAllocation;


-- add image column to ProductFeature
alter table ProductFeature add column image varchar(64) not null default '';


-- add ApprovalStatus to PO
alter table PurchOrders
drop column ARIA_POID,
add column ApprovalStatus varchar(20) not null default 'pending',
add column ApprovalReason varchar(255) not null default '';


-- add CreatedBy column to SalesOrders
alter table SalesOrders
add column CreatedBy varchar(20) not null default '',
add column SourceID bigint unsigned not null default 0;

update SalesOrders set CreatedBy = 'gumstix_com' where OrderType = 'OS';
update SalesOrders set CreatedBy = 'donnay' where OrderType != 'OS';
update SalesOrders set SourceID = replace(CustomerRef, 'OSC# ', '')
    where CustomerRef like 'OSC# %';

alter table SalesOrders
add constraint SalesOrders_fk_CreatedBy
foreign key (CreatedBy) references WWW_Users (UserID)
on delete restrict on update cascade;


-- create desired product feature records
insert into ProductFeature values ('35','DVI-D(HDMI)','','choice','','');
insert into ProductFeature values ('36','digital signal processor(DSP)','','choice','','');
insert into ProductFeature values ('37','Open GL','','choice','','');
insert into ProductFeature values ('38','Caspa Camera boards','','choice','','');
delete from ProductFeature where id='22';

insert into StockItemFeature values ('CON207M', '23', '');
insert into StockItemFeature values ('CON00090', '18', '');
insert into StockItemFeature values ('CON152', '1', '');
insert into StockItemFeature values ('CON225', '35', '');
insert into StockItemFeature values ('CON046', '38', '');
insert into StockItemFeature values ('ICP3730I', '36', '');
insert into StockItemFeature values ('ICP3730I', '37', '');
insert into StockItemFeature values ('ICP3530C72', '36', '');
insert into StockItemFeature values ('ICP3530C72', '37', '');
insert into StockItemFeature values ('ICP3730', '36', '');
insert into StockItemFeature values ('ICP3730', '37', '');
insert into StockItemFeature values ('ICP3530E', '36', '');
insert into StockItemFeature values ('ICP3530E', '37', '');
insert into StockItemFeature values ('ICL503C', '9', '');
insert into StockItemFeature values ('ICL503C', '12', '');
insert into StockItemFeature values ('ICL503', '9', '');
insert into StockItemFeature values ('ICL503', '12', '');
insert into StockItemFeature values ('CON216', '23', '');


-- add SupplierID column to users
alter table WWW_Users
drop key CustomerID,
drop key DefaultLocation,
add column SupplierID varchar(10) null default null,
modify column CustomerID bigint unsigned null default null;

update WWW_Users set CustomerID = null;

alter table WWW_Users
add constraint WWW_Users_fk_CustomerID
foreign key (CustomerID) references DebtorsMaster (DebtorNo)
on delete set null,
add constraint WWW_Users_fk_SupplierID
foreign key (SupplierID) references Suppliers (SupplierID)
on delete set null;