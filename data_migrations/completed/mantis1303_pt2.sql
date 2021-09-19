drop table if exists BankStatementPattern;
create table BankStatementPattern (
    id serial primary key,
    strategy varchar(64) not null default '',
    statementPattern varchar(255) not null default '',
    additionalStatementPattern varchar(255) not null default '',
    additionalStatementDateConstraint int unsigned null default 5,
    referencePattern varchar(255) not null default '',
    dateConstraint int unsigned null default 5,
    amountConstraint decimal(10,2) null default 0.01,
    supplierId varchar(10) null,
    expenseAccountId int unsigned null,
    updateInvoicePattern varchar(255) not null default '',
    orderBy varchar(64) not null default '',
    constraint BankStatementPattern_fk_supplierId
    foreign key (supplierId) references Suppliers (SupplierID)
    on delete restrict on update cascade,
    constraint BankStatementPattern_fk_expenseAccountId
    foreign key (expenseAccountId) references ChartMaster (AccountCode)
    on delete restrict on update cascade
);


insert into BankStatementPattern set statementPattern = '\^CHECK PAID$',
    strategy = 'Cheque',
    dateConstraint = 60;

insert into BankStatementPattern set statementPattern = '\^OTC CHECK$',
    strategy = 'Cheque',
    dateConstraint = 60;

insert into BankStatementPattern set statementPattern = '\^AMERICAN EXPRESS AXP',
    strategy = 'BankTransaction',
    referencePattern = '\^FEES.*Sweep AmEx',
    dateConstraint = 7,
    amountConstraint = null;

insert into BankStatementPattern set statementPattern = '\^ADP PAYROLL FEES',
    supplierId = 58,
    strategy = 'ExistingSupplierInvoice',
    referencePattern = '\^ADP PAYROLL FEES',
    expenseAccountId = 68500,
    dateConstraint = 5,
    updateInvoicePattern = '\^ADP PAYROLL FEES';

insert into BankStatementPattern set statementPattern = 'ADP TX/FINCL.*INC$',
    supplierId = 58,
    strategy = 'ExistingSupplierInvoice',
    additionalStatementPattern = '\^ADP TX/FINCL.*GUMSTIX$',
    additionalStatementDateConstraint = 0,
    referencePattern = 'ADP TAXES|Salaries',
    expenseAccountId = 72000,
    dateConstraint = 5,
    updateInvoicePattern = 'ADP TAXES',
    orderBy = 'SuppReference asc';

insert into BankStatementPattern set statementPattern = '\^WIRE OUT.*ADP CANADA',
    supplierId = 110,
    strategy = 'ExistingSupplierInvoice',
    referencePattern = '\^Salaries',
    updateInvoicePattern = '\^Salaries',
    expenseAccountId = 75100;

insert into BankStatementPattern set statementPattern = '\^WIRE OUT.*GUMSTIX RESEARCH',
    supplierId = 187,
    strategy = 'ExistingSupplierInvoice',
    referencePattern = '.*',
    expenseAccountId = 75000;

insert into BankStatementPattern set statementPattern = '\^AUTHNET GATEWAY',
    supplierId = 30,
    strategy = 'ExistingSupplierInvoice',
    referencePattern = '\^AUTHNET GATEWAY',
    expenseAccountId = 62000;

insert into BankStatementPattern set statementPattern = 'POS.*CARDSYSTEMS.*CHARGEBACK',
    supplierId = 30,
    strategy = 'ExistingSupplierInvoice',
    referencePattern = '\^POS CARDSYSTEMS CHARGEBACK$',
    expenseAccountId = 48000;

insert into BankStatementPattern set statementPattern = 'AMERICAN EXPRESS COLLECTION',
    supplierId = 30,
    strategy = 'CreateSupplierInvoice',
    referencePattern = 'AMERICAN EXPRESS COLLECTION',
    expenseAccountId = 62000;

insert into BankStatementPattern set statementPattern = 'ANALYSIS SERVICE CHARGE',
    supplierId = 36,
    strategy = 'CreateSupplierInvoice',
    expenseAccountId = 62000;

insert into BankStatementPattern set statementPattern = 'POS .*CCDISCOUNT',
    supplierId = 30,
    strategy = 'ExistingSupplierInvoice',
    referencePattern = 'POS CCDISCOUNT',
    expenseAccountId = 21000;

insert into BankStatementPattern set statementPattern = 'BANKCA.*CCDISCOUNT',
    supplierId = 30,
    strategy = 'ExistingSupplierInvoice',
    referencePattern = 'POS CCDISCOUNT',
    expenseAccountId = 21000;

insert into BankStatementPattern set statementPattern = '\^ANALYSIS SERVICE',
    supplierId = 36,
    strategy = 'ExistingSupplierInvoice',
    referencePattern = 'ANALYSIS SERVICE',
    expenseAccountId = 62000;

insert into BankStatementPattern set statementPattern = '\^GOOGLE.*GUMSTIX',
    supplierId = 207,
    strategy = 'CreateSupplierInvoice',
    referencePattern = 'GOOGLE ADWORDS',
    expenseAccountId = 60000;

insert into BankStatementPattern set statementPattern = '\^AMAZON',
    supplierId = 208,
    strategy = 'ExistingSupplierInvoice',
    referencePattern = 'AMAZON CLOUD',
    expenseAccountId = 68000;

insert into BankStatementPattern set statementPattern = '\^WIRE IN',
    strategy = 'SalesOrder';

insert into BankStatementPattern set statementPattern = 'BANKCARD DEPOSIT CR CD',
    strategy = 'BankTransaction',
    referencePattern = 'Sweep VIMC',
    amountConstraint = null;

insert into BankStatementPattern set statementPattern = '\^AMERICAN EXPRESS SETTLEMENT',
    strategy = 'BankTransaction',
    referencePattern = '\^Sweep AmEx',
    amountConstraint = null;

insert into BankStatementPattern set statementPattern = '.*',
    strategy = 'BankTransaction';