alter table Suppliers add column parentID bigint unsigned null default null;
alter table Suppliers
add constraint Suppliers_fk_parentID
foreign key (parentID) references Suppliers (SupplierID)
on delete restrict;