create table Manufacturer (
    id serial,
    name varchar(255) not null default '',
    conflictUrl varchar(255) not null default '',
    conflictFilename varchar(100) not null default '',
    primary key (id),
    unique key (name)
) engine=InnoDB default charset=utf8;

alter table PurchData add column manufacturerID bigint unsigned null default null after Version;
alter table PurchData add constraint PurchData_fk_manufacturerID
foreign key (manufacturerID) references Manufacturer (id)
on delete restrict;