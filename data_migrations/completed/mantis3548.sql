alter table GoodsReceivedNotice
modify column PurchaseOrderNo bigint unsigned null default null,
add column systemTypeID smallint not null default 0;

update GoodsReceivedNotice set systemTypeID = 25;


update StockProducer
set flags = 'zero_cost'
where flags = ''
and type = 'labour'
and locationID = '7'
and expectedUnitCost = 0;

update SysTypes set TypeName = 'Work Order Receipt (legacy)' where TypeID = 26;
insert into SysTypes values (32, 'Work Order Receipt', 0);
