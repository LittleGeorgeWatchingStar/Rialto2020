update StockMaster set PartValue = '' where PartValue is null;
alter table StockMaster modify column PartValue varchar(20) not null default '';