alter table SupplierInvoicePattern
add column format char(3) not null default 'pdf' after location,
add column splitPattern varchar(20) not null default '' after format,
add column parseRules text;

