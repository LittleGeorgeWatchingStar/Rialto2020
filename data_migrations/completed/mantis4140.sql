alter table DebtorTrans modify column Reference VARCHAR(100) NOT NULL default '';
alter table SalesOrderDetails ADD sourceID VARCHAR(50) NOT NULL default '';
