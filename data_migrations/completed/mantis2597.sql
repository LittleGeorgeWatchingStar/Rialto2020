alter table SuppTrans
modify column SuppReference varchar(50) not null default '',
modify column TransText text;

alter table SupplierInvoice
modify column supplierReference varchar(50) not null default '';