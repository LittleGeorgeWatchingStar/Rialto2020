CREATE TABLE Debtor_OrderAllocation (amount NUMERIC(16, 2) NOT NULL, dateUpdated DATETIME NOT NULL, ID BIGINT UNSIGNED AUTO_INCREMENT NOT NULL, creditID BIGINT UNSIGNED NOT NULL, salesOrderID BIGINT UNSIGNED NOT NULL, INDEX IDX_FE3B9EDA31E6FF97 (creditID), INDEX IDX_FE3B9EDAD537D8E8 (salesOrderID), UNIQUE INDEX UNIQ_FE3B9EDA31E6FF97D537D8E8 (creditID, salesOrderID), PRIMARY KEY(ID)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE Debtor_OrderAllocation ADD CONSTRAINT FK_FE3B9EDA31E6FF97 FOREIGN KEY (creditID) REFERENCES DebtorTrans (ID);
ALTER TABLE Debtor_OrderAllocation ADD CONSTRAINT FK_FE3B9EDAD537D8E8 FOREIGN KEY (salesOrderID) REFERENCES SalesOrders (OrderNo);

insert into Debtor_OrderAllocation
(creditID, salesOrderID, amount, dateUpdated)
  select credit.ID, credit.Order_
    , abs(credit.OvAmount + credit.OvGST + credit.OvFreight + credit.OvDiscount) as amount
    , credit.TranDate
  from DebtorTrans credit
  where credit.Type in (11, 12)
        and credit.Order_ is not null;
