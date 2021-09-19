alter table ItemVersion drop foreign key ItemVersion_fk_stockId;
alter table BOM drop foreign key `BOM_fk_Parent_ParentVersion`;
alter table WorksOrders drop foreign key `WorksOrders_fk_ItemVersion`;

alter table ItemVersion
change column stockId stockCode varchar(20) not null default '',
add column weight decimal(12,4) unsigned not null default 0,
add column volume decimal(12,4) unsigned not null default 0;

alter table ItemVersion
add CONSTRAINT `ItemVersion_fk_stockCode` FOREIGN KEY (`stockCode`)
REFERENCES `StockMaster` (`StockID`) ON DELETE CASCADE ON UPDATE CASCADE;

alter table BOM
add CONSTRAINT `BOM_fk_Parent_ParentVersion` FOREIGN KEY (`Parent`, `ParentVersion`)
REFERENCES `ItemVersion` (`stockCode`, `version`) ON DELETE CASCADE ON UPDATE CASCADE;

alter table WorksOrders
add CONSTRAINT `WorksOrders_fk_ItemVersion` FOREIGN KEY (`StockID`, `Version`)
REFERENCES `ItemVersion` (`stockCode`, `version`) on update cascade;

update ItemVersion v
join StockMaster i
on i.StockID = v.stockCode
set v.weight = i.KGS, v.volume = i.Volume;