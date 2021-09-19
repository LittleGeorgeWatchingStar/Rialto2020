CREATE TABLE Accounting_Section (id SMALLINT UNSIGNED NOT NULL, name VARCHAR(50) NOT NULL, sign SMALLINT DEFAULT 1 NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE AccountGroups CHANGE GroupName GroupName VARCHAR(30) NOT NULL, CHANGE SectionInAccounts SectionInAccounts SMALLINT UNSIGNED NOT NULL, CHANGE SequenceInTB SequenceInTB SMALLINT NOT NULL;

insert into Accounting_Section
(id, name, sign) values
(1, 'Income', -1),
(2, 'Cost Of Goods Sold', -1),
(10, 'Assets', 1),
(20, 'Liabilities', -1),
(30, 'Capital', -1),
(40, 'Retained Earnings', -1),
(90, 'Expenses', -1),
(100, 'Income taxes', -1);



ALTER TABLE AccountGroups ADD CONSTRAINT FK_F808425E426ADEDB FOREIGN KEY (SectionInAccounts) REFERENCES Accounting_Section (id);
CREATE INDEX IDX_F808425E426ADEDB ON AccountGroups (SectionInAccounts);
