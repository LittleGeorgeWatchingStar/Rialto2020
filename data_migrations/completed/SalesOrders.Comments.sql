alter table SalesOrders modify Comments text null;
alter table SalesOrders modify ShipmentType varchar(12) not null default '';
alter table SalesOrders modify SalesTaxes decimal(10,2) not null default 0;
alter table SalesOrders modify Prepayment decimal(10,2) not null default 0;
alter table SalesOrders modify ExtraLanguage tinyint not null default 0;

alter table SalesOrderDetails modify Narrative text null;
alter table SalesOrderDetails modify CustomizationID int not null default 0;