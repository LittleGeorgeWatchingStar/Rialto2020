alter table Locations
add column Active boolean not null default 1,
add column AllocateFromCM boolean not null default 0,
modify column SupplierID bigint unsigned null default null;

update Locations set SupplierID = null where SupplierID = 0;

alter table Locations
add constraint Locations_fk_SupplierID
foreign key (SupplierID) references Suppliers (SupplierID)
on delete restrict;