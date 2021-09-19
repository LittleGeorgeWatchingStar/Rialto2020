alter table BankStatements
    modify column BankStatementID serial,
    modify column BankDescription varchar(255) not null default '';

alter table BankTrans
    modify column BankTransID serial;

drop table if exists BankStatementMatch;

create table BankStatementMatch (
    bankStatementId bigint unsigned not null default 0,
    bankTransactionId bigint unsigned not null default 0,
    amountCleared decimal(10,2) not null default 0.0,
    primary key (bankStatementId, bankTransactionId),
    constraint BankStatementMatch_fk_bankStatementId
    foreign key (bankStatementId)
    references BankStatements (BankStatementID) on delete cascade,
    constraint BankStatementMatch_fk_bankTransactionId
    foreign key (bankTransactionId)
    references BankTrans (BankTransID) on delete cascade
);
