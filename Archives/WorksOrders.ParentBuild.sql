alter table WorksOrders modify column WORef int unsigned not null default 0;
alter table WorksOrders modify column ParentBuild int unsigned default null;
update WorksOrders as child 
    left join WorksOrders as parent on child.ParentBuild = parent.WOREf 
    set child.ParentBuild = null 
    where parent.WORef is null;
alter table WorksOrders 
    add foreign key (ParentBuild) references WorksOrders (WORef)
    on delete restrict on update restrict;