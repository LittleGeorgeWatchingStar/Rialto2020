ALTER TABLE CardTrans DROP INDEX CardTrans_fk_referenceTransactionID, ADD UNIQUE INDEX UNIQ_8207C1A331EDFB77 (referenceTransactionID);
ALTER TABLE CardTrans DROP FOREIGN KEY CardTrans_fk_CardID;
DROP INDEX Type ON CardTrans;
DROP INDEX CardTransID ON CardTrans;
ALTER TABLE CardTrans ADD customerID BIGINT UNSIGNED DEFAULT NULL, ADD salesOrderID BIGINT UNSIGNED DEFAULT NULL, DROP TransDate, DROP TransTime, DROP Summary, CHANGE Type Type SMALLINT DEFAULT NULL, CHANGE TransNo TransNo INT DEFAULT NULL, CHANGE TransactionID TransactionID BIGINT NOT NULL, CHANGE AuthorizationCode AuthorizationCode VARCHAR(10) NOT NULL, CHANGE Amount Amount NUMERIC(12, 2) NOT NULL, CHANGE CardID CardID VARCHAR(4) NOT NULL, CHANGE PostDate PostDate DATE NOT NULL, CHANGE Approved Approved TINYINT(1) NOT NULL, CHANGE Voided Voided TINYINT(1) NOT NULL, CHANGE Posted Posted TINYINT(1) NOT NULL;

CREATE INDEX IDX_8207C1A32CECF81796F7A896 ON CardTrans (Type, TransNo);
CREATE INDEX IDX_2C81749F2CECF81796F7A896 ON DebtorTrans (Type, TransNo);


select distinct ct.Type
from CardTrans ct
  left join DebtorTrans dt on ct.Type = dt.Type and ct.TransNo = dt.TransNo
where dt.Order_ is not null;

select count(DISTINCT ct.CardTransID), ct.Type, max(ct.dateCreated)
from CardTrans ct
  left join DebtorTrans dt on ct.Type = dt.Type and ct.TransNo = dt.TransNo
where dt.ID is null
group by ct.Type;

update CardTrans ct
  left join DebtorTrans dt on ct.Type = dt.Type and ct.TransNo = dt.TransNo
set ct.customerID = dt.customerID, ct.salesOrderID = dt.Order_;

select count(*) from CardTrans where customerID is null;
select count(*) from CardTrans where salesOrderID is null;

delete from DebtorTrans where Type = 13;

select distinct subclass from DebtorTrans;


ALTER TABLE CardTrans ADD CONSTRAINT FK_8207C1A32CECF817 FOREIGN KEY (Type) REFERENCES SysTypes (TypeID);
ALTER TABLE CardTrans ADD CONSTRAINT FK_8207C1A3CA11F76D FOREIGN KEY (customerID) REFERENCES DebtorsMaster (DebtorNo);
ALTER TABLE CardTrans ADD CONSTRAINT FK_8207C1A3D537D8E8 FOREIGN KEY (salesOrderID) REFERENCES SalesOrders (OrderNo);
CREATE INDEX IDX_8207C1A32CECF817 ON CardTrans (Type);
CREATE INDEX IDX_8207C1A3CA11F76D ON CardTrans (customerID);
CREATE INDEX IDX_8207C1A3D537D8E8 ON CardTrans (salesOrderID);
