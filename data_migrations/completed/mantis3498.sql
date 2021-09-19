alter table Shippers
add column telephone varchar(20) not null default '';

alter table ShippingMethod
add column trackingNumberRequired boolean not null default 0;