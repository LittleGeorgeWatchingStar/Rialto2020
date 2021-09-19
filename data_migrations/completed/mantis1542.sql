-- drop foreign keys
alter table BankStatementPattern
drop foreign key BankStatementPattern_fk_supplierId;

alter table PurchData
drop foreign key PurchData_fk_SupplierNo;

alter table PurchOrders
drop foreign key PurchOrders_fk_SupplierNo;

alter table SuppInvoiceDetails
drop foreign key SuppInvoiceDetails_ibfk_1;

alter table SupplierContacts
drop foreign key SupplierContacts_fk_SupplierID;

alter table WWW_Users
drop foreign key WWW_Users_fk_SupplierID;


-- modify columns
alter table Suppliers
modify column SupplierID serial;

alter table BankStatementPattern
modify column supplierId bigint unsigned null default null;

alter table PurchData
modify column SupplierNo bigint unsigned not null default 0;

alter table PurchOrders
modify column SupplierNo bigint unsigned not null default 0;

alter table SuppInvoiceDetails
modify column SupplierID bigint unsigned not null default 0;

alter table SupplierContacts
modify column SupplierID bigint unsigned not null default 0;

alter table WWW_Users
modify column SupplierID bigint unsigned null default null;


-- put foreign keys back
alter table BankStatementPattern
add constraint BankStatementPattern_fk_supplierId
foreign key (supplierId) references Suppliers (SupplierID)
on delete restrict;

alter table PurchData
add constraint PurchData_fk_SupplierNo
foreign key (SupplierNo) references Suppliers (SupplierID)
on delete restrict;

alter table PurchOrders
add constraint PurchOrders_fk_SupplierNo
foreign key (SupplierNo) references Suppliers (SupplierID)
on delete restrict;

alter table SuppInvoiceDetails
add constraint SuppInvoiceDetails_fk_SupplierID
foreign key (SupplierID) references Suppliers (SupplierID)
on delete restrict;

alter table SupplierContacts
add constraint SupplierContacts_fk_SupplierID
foreign key (SupplierID) references Suppliers (SupplierID)
on delete cascade;

alter table WWW_Users
add constraint WWW_Users_fk_SupplierID
foreign key (SupplierID) references Suppliers (SupplierID)
on delete set null;


-- fix SupplierContacts
alter table SupplierContacts
drop primary key,
add column ID serial first;

alter table SupplierContacts
add primary key (ID);