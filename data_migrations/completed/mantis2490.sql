alter table StockSerialItems
drop column unitCost;

alter table StockSerialItems
add column purchaseCost decimal(16,8) unsigned not null default 0,
add column materialCost decimal(16,8) unsigned not null default 0;