alter table PurchOrders modify column Comments text;
alter table PurchOrders add column SupplierReference varchar(50) not null default '';