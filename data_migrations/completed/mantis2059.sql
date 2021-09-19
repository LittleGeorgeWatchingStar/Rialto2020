select * from Prices where DebtorNo not in
(select DebtorNo from DebtorsMaster ) and DebtorNo != 0;

select * from Prices where StockID not in
(select StockID from StockMaster);

alter table Prices
drop primary key;

alter table Prices
add column ID serial primary key first;

alter table Prices
modify column DebtorNo bigint unsigned null default null;

update Prices set DebtorNo = null where DebtorNo = 0;

alter table Prices
drop key StockID,
drop key TypeAbbrev,
drop key CurrAbrev,
drop key DebtorNo;

alter table Prices
add unique key StockID_TypeAbbrev_CurrAbrev_DebtorNo
(StockID, TypeAbbrev, CurrAbrev, DebtorNo);

alter table Prices
add constraint Prices_fk_StockID
foreign key (StockID) references StockMaster (StockID)
on delete cascade on update cascade,
add constraint Prices_fk_TypeAbbrev
foreign key (TypeAbbrev) references SalesTypes (TypeAbbrev)
on delete cascade on update cascade,
add constraint Prices_fk_CurrAbrev
foreign key (CurrAbrev) references Currencies (CurrAbrev)
on delete cascade on update cascade,
add constraint Prices_fk_DebtorNo
foreign key (DebtorNo) references DebtorsMaster (DebtorNo)
on delete cascade;

replace into UserRole
set userId = 'geppetto', roleId = 'ROLE_SALES';