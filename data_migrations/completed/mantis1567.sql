drop table if exists SupplierAPI;

create table SupplierApi (
    supplierId bigint unsigned not null default 0,
    serviceName varchar(50) not null default '',
    primary key (supplierId),
    constraint SupplierAPI_fk_supplierId
    foreign key (supplierId) references Suppliers (SupplierID)
    on delete cascade
);

insert into SupplierApi values (3, 'rialto.purchasing.digikey_catalog');