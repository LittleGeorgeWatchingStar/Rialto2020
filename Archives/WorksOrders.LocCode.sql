select WORef, LocCode, UnitsReqd, UnitsIssued, OrderNo from WorksOrders where LocCode not in (select LocCode from Locations);

delete from WorksOrders where UnitsIssued = 0 and OrderNo = 0 and LocCode not in (select LocCode from Locations);

alter table WorksOrders
add foreign key (LocCode) references Locations (LocCode)
on delete restrict on update cascade;

select WORef, StockID, UnitsReqd, UnitsIssued, OrderNo from WorksOrders where StockID not in (select StockID from StockMaster);

update WorksOrders set StockID = 'GS200J-BT' where StockID = 'GS200J01-BT';
update WorksOrders set StockID = 'ICM025-400' where StockID = 'ICM025-416';
update WorksOrders set StockID = 'ICM026-400' where StockID = 'ICM026-416';
update WorksOrders set StockID = 'ICM026-600' where StockID = 'ICM026-624';

alter table WorksOrders
add foreign key (StockID) references StockMaster (StockID)
on delete restrict on update cascade;