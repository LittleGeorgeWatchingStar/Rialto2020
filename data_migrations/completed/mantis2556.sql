alter table StockMove modify column reference varchar(100) not null;
alter table StockMove add key systemType (systemTypeID, systemTypeNumber);