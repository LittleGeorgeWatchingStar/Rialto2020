select * from SuppInvoiceDetails where SupplierID not in ( select SupplierID from Suppliers );

select SupplierID, PONumber, Description, Price, Total, Approved, Posted, InvoiceDate from SuppInvoiceDetails where PONumber not in ( select OrderNo from PurchOrders );

select * from SuppInvoiceDetails where PONumber < 0;

delete from SuppInvoiceDetails where PONumber not in ( select OrderNo from PurchOrders );

alter table SuppInvoiceDetails
add foreign key (SupplierID) references Suppliers (SupplierID)
on delete restrict on update restrict;

alter table SuppInvoiceDetails modify PONumber int unsigned not null default 0;

alter table SuppInvoiceDetails
add foreign key (PONumber) references PurchOrders (OrderNo)
on delete restrict on update restrict;