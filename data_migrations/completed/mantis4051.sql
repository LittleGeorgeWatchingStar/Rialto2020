drop table if EXISTS ProductFeatures;

ALTER TABLE CustBranch DROP FOREIGN KEY CustBranch_fk_DebtorNo;
ALTER TABLE CustBranch DROP FOREIGN KEY CustBranch_ibfk_1;
DROP INDEX BranchCode ON CustBranch;
DROP INDEX BrName ON CustBranch;
DROP INDEX Salesman ON CustBranch;
DROP INDEX Area ON CustBranch;
DROP INDEX DefaultLocation ON CustBranch;
DROP INDEX TaxAuthority ON CustBranch;
DROP INDEX DefaultShipVia ON CustBranch;
alter table WWW_Users drop FOREIGN KEY WWW_Users_fk_BranchCode;
ALTER TABLE CustBranch DROP PRIMARY KEY;
ALTER TABLE CustBranch ADD id BIGINT UNSIGNED AUTO_INCREMENT NOT NULL PRIMARY KEY first;
alter table CustBranch MODIFY DefaultShipVia BIGINT UNSIGNED DEFAULT NULL;

alter table SalesOrders
add column branchID bigint UNSIGNED not NULL after DebtorNo;
update SalesOrders o
  join CustBranch b on o.DebtorNo = b.DebtorNo and o.BranchCode = b.BranchCode
    set o.branchID = b.id;
update SalesOrders o
  join CustBranch b on o.DebtorNo = b.DebtorNo
    set o.branchID = b.id
  where o.branchID = 0;

alter table DebtorTrans
add column branchID bigint UNSIGNED not NULL after DebtorNo;
update DebtorTrans o
  join CustBranch b on o.DebtorNo = b.DebtorNo and o.BranchCode = b.BranchCode
set o.branchID = b.id;
update DebtorTrans o
  join CustBranch b on o.DebtorNo = b.DebtorNo
set o.branchID = b.id
where o.branchID = 0;

alter table WWW_Users
add column branchID bigint UNSIGNED NULL after CustomerID;
update WWW_Users o
  join CustBranch b on o.CustomerID = b.DebtorNo and o.BranchCode = b.BranchCode
set o.branchID = b.id;

alter table StockMove
add column branchID bigint UNSIGNED NULL after customerID;
update StockMove o
  join CustBranch b on o.customerID = b.DebtorNo and o.branchCode = b.BranchCode
set o.branchID = b.id;
update StockMove m
  join DebtorTrans dt
    on m.systemTypeID = dt.Type
    and m.systemTypeNumber = dt.TransNo
  join SalesOrders o
    on dt.Order_ = o.OrderNo
  set m.branchID = o.branchID
where m.branchID is null
and m.customerID is not null;

update CustBranch set DefaultShipVia = 1 where DefaultShipVia = 0;

ALTER TABLE CustBranch ADD CONSTRAINT FK_1B238F8EDFD2DD4B FOREIGN KEY (DebtorNo) REFERENCES DebtorsMaster (DebtorNo);
ALTER TABLE CustBranch ADD CONSTRAINT FK_1B238F8E52C71AB9 FOREIGN KEY (Salesman) REFERENCES Salesman (SalesmanCode);
ALTER TABLE CustBranch ADD CONSTRAINT FK_1B238F8E77A69256 FOREIGN KEY (Area) REFERENCES Areas (AreaCode);
ALTER TABLE CustBranch ADD CONSTRAINT FK_1B238F8EA9E0653B FOREIGN KEY (DefaultLocation) REFERENCES Locations (LocCode);
ALTER TABLE CustBranch ADD CONSTRAINT FK_1B238F8EA15653E9 FOREIGN KEY (DefaultShipVia) REFERENCES Shippers (Shipper_ID);
ALTER TABLE CustBranch ADD CONSTRAINT FK_1B238F8E1E6DD779 FOREIGN KEY (TaxAuthority) REFERENCES TaxAuthorities (TaxID);
CREATE UNIQUE INDEX UNIQ_1B238F8EDFD2DD4BB119C2E7 ON CustBranch (DebtorNo, BranchCode);

ALTER TABLE DebtorTrans ADD CONSTRAINT FK_2C81749F56EC1FCE FOREIGN KEY (branchID) REFERENCES CustBranch (id);
ALTER TABLE SalesOrders ADD CONSTRAINT FK_18632A2556EC1FCE FOREIGN KEY (branchID) REFERENCES CustBranch (id);
ALTER TABLE WWW_Users ADD CONSTRAINT FK_A713D22556EC1FCE FOREIGN KEY (branchID) REFERENCES CustBranch (id);
ALTER TABLE StockMove ADD CONSTRAINT FK_F6F8B68956EC1FCE FOREIGN KEY (branchID) REFERENCES CustBranch (id);


select* from SalesOrders where branchID not in (select id from CustBranch);
select * from SalesOrders o
  left join CustBranch b on o.branchID = b.id
where o.DebtorNo != b.DebtorNo;

select * from DebtorTrans where branchID is null;
select DebtorNo, ID from DebtorTrans where branchID not in (select id from CustBranch);
select * from DebtorTrans dt
left join CustBranch b on dt.branchID = b.id
where dt.DebtorNo != b.DebtorNo;

select count(id) from StockMove
where branchID is null and ifnull(customerID, 0) != 0;


alter table SalesOrders drop column BranchCode;
alter table SalesOrders drop foreign key SalesOrders_fk_DebtorNo;
alter table SalesOrders drop column DebtorNo;
alter table DebtorTrans drop column BranchCode;
alter table DebtorTrans drop FOREIGN KEY DebtorTrans_fk_DebtorNo;
alter table DebtorTrans drop column DebtorNo;
alter table WWW_Users drop column BranchCode;
alter table WWW_Users drop FOREIGN KEY WWW_Users_fk_CustomerID;
alter table WWW_Users drop column CustomerID;
alter table StockMove drop column branchCode;
