alter table SalesOrderDetails
add column Version varchar(31) not null default '-any-'
after CustomizationID;

update SalesOrderDetails set Version = '-any-';

alter table BOM drop foreign key `BOM_ibfk_1`,
    drop foreign key `BOM_ibfk_2`;
alter table BOM add constraint `BOM_fk_Parent`
    foreign key (Parent) references StockMaster (StockID)
    on update cascade on delete cascade;
alter table BOM add constraint `BOM_fk_Component`
    foreign key (Component) references StockMaster (StockID)
    on update cascade on delete cascade;

update BOM set ComponentVersion = '-any-' where ComponentVersion = '';

alter table WORequirements
modify column Version varchar(31) not null default '-any-';
update WORequirements set Version = '-any-' where Version = '';