alter table BankStatementPattern
change column updateInvoicePattern updatePattern varchar(255) not null default '';

alter table BankStatementPattern
drop foreign key `BankStatementPattern_fk_expenseAccountId`;

alter table BankStatementPattern
change column expenseAccountId adjustmentAccountId int unsigned null default null;

alter table BankStatementPattern
add CONSTRAINT `BankStatementPattern_fk_adjustmentAccountId` FOREIGN KEY (`adjustmentAccountId`) REFERENCES `ChartMaster` (`AccountCode`) ON UPDATE CASCADE;