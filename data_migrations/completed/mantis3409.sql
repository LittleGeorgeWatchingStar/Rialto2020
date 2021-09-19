drop table if exists Filing_Entry;
create table Filing_Entry (
    id serial,
    documentID char(36) not null,
    filedBy varchar(20) not null,
    dateFiled datetime not null,
    filename varchar(50) not null default '',
    primary key (id),
    constraint Filing_Entry_fk_documentID
    foreign key (documentID) references Document (uuid)
    on delete restrict
) engine=InnoDB default charset=utf8 auto_increment=100;