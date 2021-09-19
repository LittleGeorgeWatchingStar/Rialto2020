alter table BOM modify column Quantity decimal(12,4) unsigned not null default 0;

alter table WORequirements modify column UnitsReq decimal(12,4) unsigned not null default 0;
alter table WORequirements modify column StdCost decimal(20,4) unsigned not null default 0;

alter table StandardCost modify column materialCost decimal(20,4) unsigned not null default 0;
alter table StandardCost modify column labourCost decimal(20,4) unsigned not null default 0;
alter table StandardCost modify column overheadCost decimal(20,4) unsigned not null default 0;
alter table StandardCost modify column previousCost decimal(20,4) unsigned null default null;