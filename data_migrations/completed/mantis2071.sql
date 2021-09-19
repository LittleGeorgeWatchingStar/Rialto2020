drop table if exists ItemVersion;
create table ItemVersion (
    stockId varchar(20) not null default '',
    version varchar(31) not null default '',
    active boolean not null default 1,
    primary key (stockId, version),
    constraint ItemVersion_fk_stockId
    foreign key (stockId) references StockMaster (StockID)
    on delete cascade on update cascade
) engine=InnoDB charset=utf8;

insert into ItemVersion
(stockId, version)
select distinct b.Parent as stockId, b.ParentVersion as version
from BOM b;

alter table BOM drop foreign key BOM_fk_Parent;

alter table BOM drop primary key;

alter table BOM add column ID serial primary key first;

alter table BOM
add unique key Parent_ParentVersion_Component
(Parent, ParentVersion, Component);

alter table BOM
add constraint BOM_fk_Parent_ParentVersion
foreign key (Parent, ParentVersion)
references ItemVersion (stockId, version)
on delete cascade on update cascade;