alter table CATaxRegimes drop primary key;

alter table CATaxRegimes
add column id serial primary key first,
add column startDate date,
modify column County varchar(50) not null default '' after id,
modify column City varchar(50) not null default '' after County,
modify column Rate double not null default 0 after startDate;

alter table CATaxRegimes
add constraint County_City_startDate
unique key (County, City, startDate);