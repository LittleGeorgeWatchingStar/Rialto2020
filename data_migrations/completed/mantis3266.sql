alter table SupplierInvoice
add column supplierID bigint unsigned not null after id;

update SupplierInvoice inv
join PurchOrders po
     on inv.purchaseOrderID = po.OrderNo
set inv.supplierID = po.supplierNo;

alter table SupplierInvoice
add constraint SupplierInvoice_fk_supplierID
foreign key (supplierID) references Suppliers (SupplierID)
on delete restrict;

alter table SupplierInvoice
modify column purchaseOrderID bigint unsigned null default null;

alter table SupplierInvoice
add constraint SupplierInvoice_fk_purchaseOrderID
foreign key (purchaseOrderID) references PurchOrders (OrderNo)
on delete restrict;

show create table SupplierInvoice\G

alter table SuppInvoiceDetails
modify column PONumber bigint unsigned null default null;