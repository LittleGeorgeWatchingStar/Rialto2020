alter table SalesOrders
add constraint SalesOrders_fk_FromStkLoc
foreign key (FromStkLoc) references Locations (LocCode)
on delete restrict on update cascade;