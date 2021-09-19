drop table if exists PrintJob;
create table PrintJob (
    id serial,
    dateCreated datetime not null,
    datePrinted datetime null default null,
    format varchar(20) not null,
    `data` mediumblob,
    numCopies smallint unsigned not null default 1,
    error varchar(255) null default null,
    primary key (id)
) engine=InnoDB default charset=utf8;