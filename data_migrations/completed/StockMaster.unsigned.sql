alter table StockMaster
modify column KGS decimal(12,4) unsigned not null default 0,
modify column Volume decimal(12,4) unsigned not null default 0,
modify column ActualCost decimal(20,4) unsigned not null default 0,
modify column LastCost decimal(20,4) unsigned not null default 0,
modify column Materialcost decimal(20,4) unsigned not null default 0,
modify column Labourcost decimal(20,4) unsigned not null default 0,
modify column Overheadcost decimal(20,4) unsigned not null default 0;

show warnings;