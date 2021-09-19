alter table Manufacturer
add column smelterData boolean not null default 0,
add column policy varchar(20) not null default 'N/A';