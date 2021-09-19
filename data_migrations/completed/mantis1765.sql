drop table if exists Document;
create table Document (
    uuid char(36) not null,
    name varchar(255) not null default '',
    dateCreated datetime not null,
    dateUpdated datetime not null,
    templateFilename varchar(255) not null default '',
    scheduleDay smallint not null default 0,
    scheduleMonths varchar(50) not null default '',
    primary key (uuid),
    unique key (name)
) engine=InnoDB default charset=utf8;

alter table Forms add column documentID char(36) not null after FieldID;
alter table Forms modify column FormID varchar(100) not null default '';
alter table Forms modify column FormField varchar(64) not null default '';
alter table Forms modify column ToSelect varchar(255) not null default '';
alter table Forms modify column WhereCriterion varchar(255) not null default '';

insert into Document
(uuid, name, dateCreated, dateUpdated, templateFilename)
select distinct uuid(), FormID, now(), now(), FormID from Forms;

update Forms f
join Document d on d.name = f.FormID
set f.documentID = d.uuid;

alter table Forms
add constraint Forms_fk_documentID
foreign key (documentID) references Document (uuid)
on delete cascade;