drop table if exists Requirement;
create table Requirement (
    id serial,
    oldID bigint unsigned not null,
    consumerType varchar(20),
    consumerID bigint unsigned not null default 0,
    stockCode varchar(20) not null default '',
    version varchar(31) not null default '',
    customizationID bigint unsigned null default null,
    unitQtyNeeded decimal(12,4) unsigned not null default 1,
    scrapCount decimal(12,4) unsigned not null default 0,
    unitStandardCost decimal(20,4) unsigned not null default 0,
    primary key (id),
    unique key consumer_item (consumerType, consumerID, stockCode),
    constraint StockRequest_fk_stockCode
    foreign key (stockCode) references StockMaster (StockID)
    on update cascade on delete restrict,
    constraint StockRequest_fk_customizationID
    foreign key (customizationID) references Customization (id)
    on delete restrict
) engine=InnoDB default charset=utf8;

alter table StockAllocation
add column requirementID bigint unsigned not null default 0 after requestID;
alter table StockAllocation drop foreign key `StockAllocation_fk_requestID`;
alter table StockAllocation drop key request_source;

alter table StockMove
add column parentItem varchar(20) null default null;
alter table StockMove
add constraint StockMove_fk_parentItem
foreign key (parentItem) references StockMaster (StockID)
on update cascade on delete restrict;

create table SalesInvoiceItem (
    id serial,
    debtorTransID bigint unsigned not null,
    orderItemID bigint unsigned not null,
    qtyInvoiced decimal(16,4) not null,
    unitPrice decimal(16,4) not null,
    taxRate decimal(8,6) unsigned not null default 0,
    discountRate decimal(8,6) unsigned not null default 0,
    primary key (id),
    constraint SalesInvoiceItem_fk_debtorTransID
    foreign key (debtorTransID) references DebtorTrans (ID)
    on delete cascade,
    constraint SalesInvoiceItem_fk_orderItemID
    foreign key (orderItemID) references SalesOrderDetails (ID)
    on delete restrict
) engine=InnoDB default charset=utf8;

