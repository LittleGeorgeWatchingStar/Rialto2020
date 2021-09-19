select BranchCode, DebtorNo, BrName, BrState, ContactName, TaxAuthority from CustBranch where TaxAuthority not in ( select TaxID from TaxAuthorities );

update CustBranch set TaxAuthority = 1 where BrState = 'CA' and TaxAuthority not in ( select TaxID from TaxAuthorities );

update CustBranch set TaxAuthority = 1 where BrState = 'California' and TaxAuthority not in ( select TaxID from TaxAuthorities );

update CustBranch set TaxAuthority = 0 where TaxAuthority not in ( select TaxID from TaxAuthorities );

select BranchCode, DebtorNo, BrName, BrState, ContactName, TaxAuthority from CustBranch where TaxAuthority not in ( select TaxID from TaxAuthorities );

alter table TaxAuthorities modify column TaxID tinyint unsigned not null default 0;
alter table CustBranch modify column TaxAuthority tinyint unsigned not null default 0;

alter table CustBranch add foreign key (TaxAuthority) references TaxAuthorities (TaxID) on delete restrict on update cascade;