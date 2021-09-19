drop table if exists `Role`;
create table `Role` (
    id varchar(50) not null default '' primary key
) default charset=utf8;

insert into `Role`
select distinct roleId from UserRole;

alter table `UserRole`
add constraint UserRole_fk_roleId
foreign key (roleId) references Role (id)
on update cascade on delete cascade;

alter table WWW_Users
modify column BranchCode varchar(10) null default null;

update WWW_Users
set BranchCode = null where BranchCode = '';

alter table WWW_Users
add constraint WWW_Users_fk_BranchCode
foreign key (CustomerID, BranchCode) references CustBranch (DebtorNo, BranchCode)
on delete restrict;

alter table WWW_Users
add constraint WWW_Users_fk_DefaultLocation
foreign key (DefaultLocation) references Locations (LocCode)
on delete restrict;

alter table DebtorsMaster
modify column SalesType char(2) null default null;

update DebtorsMaster
set SalesType = null where SalesType = '';

alter table DebtorsMaster
add constraint DebtorsMaster_fk_SalesType
foreign key (SalesType) references SalesTypes (TypeAbbrev)
on delete restrict on update cascade;

update DebtorsMaster set StateStatus = 'Taxable' where StateStatus = '';

alter table DebtorsMaster modify column StateStatus varchar(31) not null default 'Taxable';

alter table StockMoves
modify column DebtorNo bigint unsigned null default null,
modify column BranchCode varchar(10) null default null,
modify column DiscountAccount int unsigned null default null;

alter table GRNs
modify column ItemCode varchar(20) null default null;
update GRNs set ItemCode = null where ItemCode = '';
alter table GRNs
drop foreign key GRNs_ibfk_1,
add constraint GRNs_fk_GRNBatch
foreign key (GRNBatch) references GoodsReceivedNotice (BatchID)
on delete cascade,
add constraint GRNs_fk_ItemCode
foreign key (ItemCode) references StockMaster (StockID);

update StockMoves
set DebtorNo = null where DebtorNo = 0;
update StockMoves
set BranchCode = null where BranchCode = '';
update StockMoves
set DiscountAccount = null where DiscountAccount = 0;

alter table StockMoves
add constraint StockMoves_fk_DebtorNo
foreign key (DebtorNo) references DebtorsMaster (DebtorNo);

alter table StockMoves
add constraint StockMoves_fk_DiscountAccount
foreign key (DiscountAccount) references ChartMaster (AccountCode);

alter table DebtorTrans
modify column Order_ bigint unsigned null default null,
modify column ShipVia bigint unsigned null default null,
modify column TranDate datetime not null;

update DebtorTrans set Order_ = null where Order_ = 0;
update DebtorTrans set ShipVia = null where ShipVia = 0;

alter table DebtorTrans
add constraint DebtorTrans_fk_ShipVia
foreign key (ShipVia) references Shippers (Shipper_ID);

alter table SalesOrderDetails
modify column DiscountAccount int unsigned null default null;
update SalesOrderDetails
set DiscountAccount = null where DiscountAccount = 0;
alter table SalesOrderDetails
add constraint SalesOrderDetails_fk_DiscountAccount
foreign key (DiscountAccount) references ChartMaster (AccountCode);


alter table PurchOrders
modify column OrdDate timestamp null default CURRENT_TIMESTAMP,
modify column DatePrinted datetime null default null;

update PurchOrders set OrdDate = null where OrdDate = 0;
update PurchOrders set DatePrinted = null where DatePrinted = 0;

alter table PurchOrderDetails
modify column RequestedDate date null default null,
modify column CommitmentDate date null default null,
modify column DeliveryDate date null default null;

update PurchOrderDetails set RequestedDate = null where RequestedDate = 0;
update PurchOrderDetails set CommitmentDate = null where CommitmentDate = 0;
update PurchOrderDetails set DeliveryDate = null where DeliveryDate = 0;

alter table CardTrans
modify column PostDate date null default null;

alter table SalesOrders
modify column DateToShip datetime null default null,
modify column DeliveryDate date null default null,
modify column DatePackingSlipPrinted date null default null;

update SalesOrders set DateToShip = null where DateToShip = 0;
update SalesOrders set DeliveryDate = null where DeliveryDate = 0;
update SalesOrders set DatePackingSlipPrinted = null where DatePackingSlipPrinted = 0;

alter table SalesOrderDetails
modify column ActualDispatchDate datetime null default null;
update SalesOrderDetails set ActualDispatchDate = null where ActualDispatchDate = 0;

alter table SuppTrans
modify column TranDate datetime not null,
modify column DueDate date null default null;
update SuppTrans set DueDate = null where DueDate = 0;

alter table RecurringInvoices
modify column RecurringID serial,
modify column SupplierNo bigint unsigned not null default 0,
add constraint RecurringInvoices_fk_SupplierNo
foreign key (SupplierNo) references Suppliers (SupplierID)
on delete restrict;

alter table SuppTrans
modify column ID serial,
modify column SupplierNo bigint unsigned not null default 0,
modify column RecurringTransID bigint unsigned null default null;

update SuppTrans set RecurringTransID = null where RecurringTransID = 0;

alter table WorksOrders
modify column RequiredBy date null default null,
modify column ReleasedDate datetime null default null;

update WorksOrders set RequiredBy = null where RequiredBy = 0;
update WorksOrders set ReleasedDate = null where ReleasedDate = 0;

alter table LocTransferHeader
modify column DateReceived datetime null default null;
update LocTransferHeader
set DateReceived = null where DateReceived = 0;

alter table LocTransfers
modify column RecDate datetime null default null,
modify column ShipDate date null default null;
update LocTransfers set RecDate = null where RecDate = 0;
update LocTransfers set ShipDate = null where ShipDate = 0;CREATE TABLE `Customization` (
    `id` serial,
    `name` varchar(64) NOT NULL,
    `stockId` varchar(20) NOT NULL,
    PRIMARY KEY (`ID`),
    constraint Customization_fk_stockId FOREIGN KEY (stockId)
        REFERENCES StockMaster (StockID)
        on update cascade on delete cascade
) ENGINE=InnoDB AUTO_INCREMENT=189 DEFAULT CHARSET=utf8;

insert into Customization
(`id`, `name`, `stockId`)
select distinct `CustomizationID`, `Name`, `ParentID`
from Customizations c join Names n
on c.CustomizationID = n.TypeNo;

CREATE TABLE `CustomizationToSubstitution` (
    `customizationId` bigint(20) unsigned NOT NULL,
    `substitutionId` int(11) NOT NULL,
    PRIMARY KEY (`customizationId`, `substitutionId`),
    constraint CustomizationToSubstitution_fk_customizationId FOREIGN KEY (`customizationId`)
        REFERENCES Customization (id)
        on update cascade on delete cascade,
    constraint CustomizationToSubstitution_fk_substitutionId FOREIGN KEY (`substitutionId`)
        REFERENCES Substitutions (ID)
        on update cascade on delete cascade
) ENGINE=InnoDB AUTO_INCREMENT=189 DEFAULT CHARSET=utf8;

insert into CustomizationToSubstitution
(`customizationId`, `substitutionId`)
select distinct `CustomizationID`, `SubstitutionID` from Customizations
where `substitutionId` in
(select distinct ID from Substitutions);

alter table WORequirements drop WrkCentre;
alter table WORequirements drop LocCode;
alter table WORequirements drop UnitsIssued;

alter table SalesOrderDetails
modify column CustomizationID bigint unsigned null default null;

update SalesOrderDetails
set CustomizationID = null where CustomizationID = 0;

select distinct CustomizationID from SalesOrderDetails
where CustomizationID not in (select id from Customization)
and CustomizationID is not null;

alter table SalesOrderDetails
add constraint SalesOrderDetails_fk_CustomizationID
foreign key (CustomizationID) references Customization (id)
on delete restrict;


alter table WorksOrders
modify column CustomizationID bigint unsigned null default null;

update WorksOrders
set CustomizationID = null where CustomizationID = 0;

select distinct CustomizationID from WorksOrders
where CustomizationID not in (select id from Customization)
and CustomizationID is not null;

alter table WorksOrders
add constraint WorksOrders_fk_CustomizationID
foreign key (CustomizationID) references Customization (id)
on delete restrict;select SerialNo, StockID, group_concat(distinct RecLoc), count(SerialNo)
from LocTransfers
where SerialNo not in (select SerialNo from StockSerialItems)
group by SerialNo;

insert into StockSerialItems
(StockID, LocCode, SerialNo)
select StockID, '7', SerialNo
from LocTransfers
where SerialNo not in (select SerialNo from StockSerialItems)
and SerialNo != 0
group by SerialNo;

alter table LocTransfers
drop primary key,
drop foreign key LocTransfers_ibfk_1;

alter table LocTransfers
add column ID serial primary key first;

alter table LocTransfers
modify column SerialNo int(30) null default null;

update LocTransfers
set SerialNo = null where SerialNo = 0;

alter table LocTransfers
add constraint LocTransfers_fk_Reference
foreign key (Reference) references LocTransferHeader (ID)
on delete restrict on update restrict;

alter table LocTransfers
add constraint LocTransfers_fk_SerialNo
foreign key (SerialNo) references StockSerialItems (SerialNo)
on delete restrict on update restrict;

alter table LocTransfers
add unique key Reference_StockID_SerialNo (Reference, StockID, SerialNo);

alter table BankStatementPattern
change column orderBy sortOrder int not null default 0;