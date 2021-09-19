select count(ID), StockID, TypeAbbrev, CurrAbrev
from Prices group by StockID, TypeAbbrev, CurrAbrev;

create temporary table Keepers select max(ID) as ID from Prices
group by StockID, TypeAbbrev, CurrAbrev;

delete from Prices where ID not in (select ID from Keepers);

alter table Prices drop foreign key Prices_fk_DebtorNo;
alter table Prices drop foreign key Prices_fk_CurrAbrev;
alter table Prices drop foreign key Prices_fk_StockID;
alter table Prices drop foreign key Prices_fk_TypeAbbrev;
alter table Prices drop KEY `StockID_TypeAbbrev_CurrAbrev_DebtorNo`;
alter table Prices drop column DebtorNo, drop column BranchCode;

alter table Prices add unique key StockID_TypeAbbrev_CurrAbrev (StockID, TypeAbbrev, CurrAbrev);

alter table Prices
add CONSTRAINT `Prices_fk_CurrAbrev` FOREIGN KEY (`CurrAbrev`) REFERENCES `Currencies` (`CurrAbrev`) ON DELETE CASCADE ON UPDATE CASCADE,
add CONSTRAINT `Prices_fk_StockID` FOREIGN KEY (`StockID`) REFERENCES `StockMaster` (`StockID`) ON DELETE CASCADE ON UPDATE CASCADE,
add CONSTRAINT `Prices_fk_TypeAbbrev` FOREIGN KEY (`TypeAbbrev`) REFERENCES `SalesTypes` (`TypeAbbrev`) ON DELETE CASCADE ON UPDATE CASCADE;
