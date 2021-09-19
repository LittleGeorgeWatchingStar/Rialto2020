alter table LocTransfers
drop foreign key LocTransfers_fk_SerialNo;

alter table StockSerialItems modify column SerialNo serial first;

alter table LocTransfers
modify column SerialNo bigint unsigned null default null;

alter table LocTransfers
add CONSTRAINT `LocTransfers_fk_SerialNo`
FOREIGN KEY (`SerialNo`) REFERENCES `StockSerialItems` (`SerialNo`);