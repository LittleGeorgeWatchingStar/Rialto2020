-- loosen
alter table DebtorTrans
add column subclass varchar(10) not null after Type,
add column customerID bigint UNSIGNED null after subclass,
modify column Consignment varchar(22) not null default '';

update CustAllocns set DateAlloc = '2004-01-01' where DateAlloc = '0000-00-00';

ALTER TABLE CustAllocns
  CHANGE ID ID BIGINT UNSIGNED AUTO_INCREMENT NOT NULL,
  CHANGE Amt Amt NUMERIC(16, 2) NOT NULL,
  CHANGE DateAlloc DateAlloc DATE NOT NULL,
  CHANGE TransID_AllocFrom TransID_AllocFrom BIGINT UNSIGNED DEFAULT NULL,
  CHANGE TransID_AllocTo TransID_AllocTo BIGINT UNSIGNED DEFAULT NULL;
DROP INDEX AllocFrom_AllocTo ON CustAllocns;
DROP INDEX DateAlloc ON CustAllocns;


-- update
update CustAllocns
set TransID_AllocFrom = NULL
where TransID_AllocFrom not in (select ID from DebtorTrans);

update CustAllocns
set TransID_AllocTo = NULL
where TransID_AllocTo not in (select ID from DebtorTrans);

update DebtorTrans t
  join CustBranch b on t.branchID = b.id
set t.customerID = b.DebtorNo;

update DebtorTrans set subclass = 'invoice' where Type in (10, 101);
update DebtorTrans set subclass = 'credit' where Type in (11, 12);
update DebtorTrans set subclass = 'other' where Type not in (10, 11, 12, 101);

select * from DebtorTrans where customerID not in (select DebtorNo from DebtorsMaster);

update DebtorTrans set Order_ = NULL where Order_ not in (select OrderNo from SalesOrders);


-- tighten
ALTER TABLE CustAllocns ADD CONSTRAINT FK_F573F878D2837C1 FOREIGN KEY (TransID_AllocFrom) REFERENCES DebtorTrans (ID);
ALTER TABLE CustAllocns ADD CONSTRAINT FK_F573F872BA0BF7F FOREIGN KEY (TransID_AllocTo) REFERENCES DebtorTrans (ID);
CREATE UNIQUE INDEX UNIQ_F573F878D2837C12BA0BF7F ON CustAllocns (TransID_AllocFrom, TransID_AllocTo);

ALTER TABLE DebtorTrans DROP FOREIGN KEY FK_2C81749F56EC1FCE;
DROP INDEX ID ON DebtorTrans;
DROP INDEX Tpe ON DebtorTrans;
DROP INDEX Settled ON DebtorTrans;
DROP INDEX TranDate ON DebtorTrans;
DROP INDEX TransNo ON DebtorTrans;
DROP INDEX Type_2 ON DebtorTrans;
DROP INDEX EDISent ON DebtorTrans;
DROP INDEX FK_2C81749F56EC1FCE ON DebtorTrans;
ALTER TABLE DebtorTrans DROP branchID, CHANGE customerID customerID BIGINT UNSIGNED NOT NULL;
ALTER TABLE DebtorTrans ADD CONSTRAINT FK_2C81749FCA11F76D FOREIGN KEY (customerID) REFERENCES DebtorsMaster (DebtorNo);
ALTER TABLE DebtorTrans ADD CONSTRAINT FK_2C81749FD05B943B FOREIGN KEY (Order_) REFERENCES SalesOrders (OrderNo);
ALTER TABLE DebtorTrans ADD CONSTRAINT FK_2C81749FC42B4997 FOREIGN KEY (Prd) REFERENCES Periods (PeriodNo);
ALTER TABLE DebtorTrans ADD CONSTRAINT FK_2C81749F2CECF817 FOREIGN KEY (Type) REFERENCES SysTypes (TypeID);
CREATE INDEX IDX_2C81749FCA11F76D ON DebtorTrans (customerID);
