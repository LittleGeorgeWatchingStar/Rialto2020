alter table Shippers
modify column Shipper_ID serial,
modify column Active boolean not null default 0,
add column AccountNo varchar(30) after ShipperName;

update Shippers set AccountNo = '7Y284V' where Shipper_ID = 1;

alter table SalesOrders
modify column ShipVia bigint unsigned null default null;

alter table SalesOrders
add constraint SalesOrders_fk_ShipVia
foreign key (ShipVia) references Shippers (Shipper_ID)
on delete restrict;

alter table PurchOrders
add column ShipperID bigint unsigned null default null after Country,
add column ShippingMethod varchar(12) not null default '' after ShipperID;

alter table PurchOrders
add constraint PurchOrders_fk_ShipperID
foreign key (ShipperID) references Shippers (Shipper_ID)
on delete restrict;