select * from SuppAllocs
where TransID_AllocFrom not in ( select ID from SuppTrans );

select * from SuppAllocs
where TransID_AllocTo not in ( select ID from SuppTrans );

alter table SuppAllocs
modify column ID serial,
modify column Amt decimal(16,4) not null default 0,
modify column TransID_AllocFrom bigint unsigned not null default 0,
modify column TransID_AllocTo bigint unsigned not null default 0;

delete from SuppAllocs
where TransID_AllocFrom not in ( select ID from SuppTrans );

delete from SuppAllocs
where TransID_AllocTo not in ( select ID from SuppTrans );

alter table SuppAllocs
add constraint SuppAllocs_fk_From
foreign key (TransID_AllocFrom) references SuppTrans (ID)
on delete cascade,
add constraint SuppAllocs_fk_To
foreign key (TransID_AllocTo) references SuppTrans (ID)
on delete cascade;

alter table BankStatementMatch drop foreign key BankStatementMatch_fk_bankStatementId;
alter table BankStatementMatch drop foreign key BankStatementMatch_fk_bankTransactionId;
alter table BankStatementMatch drop key BankStatementMatch_fk_bankTransactionId;
alter table BankStatementMatch drop primary key;
alter table BankStatementMatch
change column bankStatementId statementID bigint unsigned not null default 0,
change column bankTransactionId transactionID bigint unsigned not null default 0;
alter table BankStatementMatch add unique key statementID_transactionID (statementID, transactionID);
alter table BankStatementMatch add column id serial first;
alter table BankStatementMatch add primary key (id);
alter table BankStatementMatch add constraint BankStatementMatch_fk_statementID
    foreign key (statementID)
    references BankStatements (BankStatementID) on delete cascade;
delete from BankStatementMatch where transactionID not in (select BankTransID from BankTrans);
alter table BankStatementMatch add constraint BankStatementMatch_fk_transactionID
    foreign key (transactionID)
    references BankTrans (BankTransID) on delete cascade;
