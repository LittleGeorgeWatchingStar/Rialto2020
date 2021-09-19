drop table if exists ChangeNoticeItem;
drop table if exists ChangeNotice;

create table ChangeNotice (
    id serial,
    dateCreated datetime not null,
    effectiveDate date not null,
    description text,
    postID int unsigned not null default 0,
    primary key (id)
) engine=InnoDB default charset=utf8 auto_increment=100;

create table ChangeNoticeItem (
    id serial,
    changeNoticeID bigint unsigned not null,
    stockCode varchar(20) not null,
    version varchar(31) not null default '',
    primary key (id),
    unique key (changeNoticeID, stockCode, version),
    constraint ChangeNoticeItem_fk_changeNoticeID
    foreign key (changeNoticeID) references ChangeNotice (id)
    on delete cascade,
    constraint ChangeNoticeItem_fk_stockCode
    foreign key (stockCode) references StockMaster (StockID)
    on delete cascade on update cascade
) engine=InnoDB default charset=utf8;

alter table CmsEntry add column format char(4) not null default 'html' after id;