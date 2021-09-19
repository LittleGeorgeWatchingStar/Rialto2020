alter table Substitutions
modify column ParentID varchar(20) null default null,
modify column Instructions varchar(200) not null default '',
modify column PriceChange decimal(12,2) not null default 0,
modify column WorkCenter varchar(5) null default null;

update Substitutions set ParentID = null where ParentID = '';