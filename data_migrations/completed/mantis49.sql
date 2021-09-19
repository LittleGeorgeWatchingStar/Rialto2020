alter table SupplierContacts add constraint SupplierContacts_fk_SupplierID
    foreign key (SupplierID) references Suppliers (SupplierID)
    on update cascade on delete cascade,
modify column StatContact boolean not null default 0,
add column KitContact boolean not null default 0;