update StockMaster set Package = '' where Package is null;

alter table StockMaster
modify column Package varchar(50) not null default '';