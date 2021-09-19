ALTER TABLE WorksOrders DROP FOREIGN KEY `WorksOrders_ibfk_1`;
ALTER TABLE WorksOrders DROP FOREIGN KEY `WorksOrders_ibfk_2`;
ALTER TABLE WorksOrders DROP FOREIGN KEY `WorksOrders_ibfk_3`;

alter table SalesReturnItem drop foreign key `SalesReturnItem_fk_workOrder`;

alter table WorksOrders modify column WORef serial,
    modify column ParentBuild bigint unsigned null;

alter table WorksOrders add constraint WorksOrders_fk_ParentBuild
    foreign key (ParentBuild) references WorksOrders (WORef)
    on delete restrict;

alter table WorksOrders add constraint WorksOrders_fk_LocCode
    foreign key (LocCode) references Locations (LocCode)
    on delete restrict;

alter table WorksOrders add constraint WorksOrders_fk_StockID
    foreign key (StockID) references StockMaster (StockID)
    on delete restrict;

alter table SalesReturnItem modify column workOrder bigint unsigned default null;

alter table SalesReturnItem add CONSTRAINT `SalesReturnItem_fk_workOrder`
    FOREIGN KEY (`workOrder`) REFERENCES `WorksOrders` (`WORef`)
    on delete restrict;
