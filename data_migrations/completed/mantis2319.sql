drop table if exists StockCounts;
drop table if exists BinCountSelectedAllocation;
drop table if exists BinCount;
drop table if exists StockCount;

create table StockCount (
    id bigint unsigned not null auto_increment,
    locationID char(5) not null default '',
    requestedBy varchar(20) not null default '',
    dateRequested datetime not null,
    primary key (id),
    constraint StockCount_fk_locationID
    foreign key (locationID) references Locations (LocCode),
    constraint StockCount_fk_requestedBy
    foreign key (requestedBy) references WWW_Users (UserID)
) engine=InnoDB default charset=utf8 auto_increment=100;

create table BinCount (
    id bigint unsigned not null auto_increment,
    stockCountID bigint unsigned not null,
    binID bigint unsigned not null,
    qtyAtRequest int unsigned not null default 0,
    qtyAtCount int unsigned null default null,
    reportedQty int unsigned null default null,
    dateUpdated datetime null default null,
    acceptedQty int unsigned null default null,
    dateApproved datetime null default null,
    primary key (id),
    constraint BinCount_fk_stockCountID
    foreign key (stockCountID) references StockCount (id) on delete cascade,
    constraint BinCount_fk_binID
    foreign key (binID) references StockSerialItems (SerialNo)
) engine=InnoDB default charset=utf8 auto_increment=100;

create table BinCountSelectedAllocation (
    binCountID bigint unsigned not null,
    allocationID bigint unsigned not null,
    primary key (binCountID, allocationID),
    constraint BinCountSelectedAllocation_fk_binCountID
    foreign key (binCountID) references BinCount (id)
    on delete cascade,
    constraint BinCountSelectedAllocation_fk_allocationID
    foreign key (allocationID) references StockAllocation (AllocationID)
    on delete cascade
) engine=InnoDB default charset=utf8;