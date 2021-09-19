alter table SalesOrderDetails drop foreign key SalesOrderDetails_ibfk_1;
alter table SalesOrders modify OrderNo serial;
alter table SalesOrderDetails modify OrderNo bigint unsigned not null default 0;
alter table SalesOrderDetails add constraint SalesOrderDetails_fk_OrderNo
    foreign key (OrderNo) references SalesOrders (OrderNo)
    on delete cascade;
alter table SalesOrderDetails modify column ID serial;

alter table CustBranch drop foreign key CustBranch_ibfk_2;
alter table CustBranch modify DebtorNo bigint unsigned not null default 0;
alter table SalesOrders drop foreign key SalesOrders_ibfk_1;
alter table SalesOrders modify DebtorNo bigint unsigned not null default 0;
alter table DebtorsMaster modify DebtorNo serial;
alter table CustBranch add constraint CustBranch_fk_DebtorNo
    foreign key (DebtorNo) references DebtorsMaster (DebtorNo)
    on delete cascade;
alter table SalesOrders add constraint SalesOrders_fk_DebtorNo
    foreign key (DebtorNo) references DebtorsMaster (DebtorNo)
    on delete restrict;

alter table DebtorTrans modify column ID serial;
alter table DebtorTrans modify column DebtorNo bigint unsigned not null default 0;
alter table DebtorTrans add constraint DebtorTrans_fk_DebtorNo
    foreign key (DebtorNo) references DebtorsMaster (DebtorNo) on delete restrict;

drop table if exists SalesReturnLineItem;
drop table if exists SalesReturn;

create table SalesReturn (
    id serial,
    authorizedBy varchar(20) not null default '',
    originalInvoice bigint unsigned not null default 0,
    dateAuthorized datetime not null default 0,
    caseNumber int unsigned not null default 0,
    replacementOrder bigint unsigned default null,
    primary key (id),
    constraint SalesReturn_fk_authorizedBy foreign key (authorizedBy)
        references WWW_Users (UserID)
        on update cascade on delete restrict,
    constraint SalesReturn_fk_originalInvoice foreign key (originalInvoice)
        references DebtorTrans (ID)
        on delete restrict,
    constraint SalesReturn_fk_replacementOrder foreign key (replacementOrder)
        references SalesOrders (OrderNo)
        on delete restrict
) auto_increment = 2718;

create table SalesReturnLineItem (
    id serial,
    salesReturn bigint unsigned not null default 0,
    stockItem varchar(20) not null default '',
    qtyAuthorized int unsigned not null default 0,
    qtyReceived int unsigned not null default 0,
    passDisposition varchar(50) not null default '',
    failDisposition varchar(50) not null default '',
    primary key (id),
    constraint SalesReturnLineItem_fk_salesReturn foreign key (salesReturn)
        references SalesReturn (id)
        on delete cascade,
    constraint SalesReturnLineItem_fk_stockItem foreign key (stockItem)
        references StockMaster (StockID)
        on delete restrict on update cascade
) auto_increment = 6022;

