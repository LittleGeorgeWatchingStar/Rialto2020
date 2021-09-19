update StockMaster set ECCN_Code = '' where ECCN_Code is null;
alter table StockMaster
modify column ECCN_Code varchar(12) not null default '';