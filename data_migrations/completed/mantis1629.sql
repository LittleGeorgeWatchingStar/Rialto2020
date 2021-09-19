alter table Forms modify column FieldID serial;
alter table Forms modify column FormID varchar(100) not null default '';
alter table Forms modify column `A` varchar(50) not null default '';
update Forms set FormID = 'sed.png' where FormID = 'sed';