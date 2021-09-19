create table SupplierInvoice (
    id serial,
    purchaseOrderID bigint unsigned not null default 0,
    supplierReference varchar(20) not null default '',
    invoiceDate datetime not null,
    totalCost decimal(12,2) not null default 0,
    approved boolean not null default 0,
    filename varchar(200) not null default '',
    primary key (id),
    unique key (purchaseOrderID, supplierReference)
) engine=InnoDB default charset=utf8;

insert into SupplierInvoice
(purchaseOrderID, supplierReference, invoiceDate, totalCost, approved)
select PONumber, SuppReference, InvoiceDate, sum(Total), max(Approved)
from SuppInvoiceDetails
where SuppInvoiceDetails.Description != 'CHECKSUM'
group by PONumber, SuppReference;

alter table GRNs
drop foreign key `GRNs_fk_GRNBatch`;

alter table GoodsReceivedNotice
modify column BatchID serial,
modify column PurchaseOrderNo bigint unsigned not null default 0;

alter table GRNs
modify column GRNNo serial,
modify column GRNBatch bigint unsigned not null default 0,
modify column PODetailItem bigint unsigned not null default 0;

alter table GRNs
add CONSTRAINT `GRNs_fk_GRNBatch` FOREIGN KEY (`GRNBatch`)
REFERENCES `GoodsReceivedNotice` (`BatchID`) ON DELETE CASCADE;

alter table PurchOrderDetails
modify column PODetailItem serial;

alter table SuppInvoiceDetails
modify column SIDetailID serial,
modify column LineNo int not null default 0,
modify column StockID varchar(20) null default null,
modify column GRNNo bigint unsigned null default null,
modify column GLCode int unsigned null default null,
modify column Approved boolean not null default 0,
modify column Posted boolean not null default 0,
add column invoiceID bigint unsigned null default null;

update SuppInvoiceDetails d
join SupplierInvoice i
    on d.PONumber = i.purchaseOrderID
    and d.SuppReference = i.supplierReference
set invoiceID = i.id;

alter table SuppInvoiceDetails
add constraint SuppInvoiceDetails_fk_invoiceID
foreign key (invoiceID) references SupplierInvoice (id)
on delete cascade;

select distinct StockID from SuppInvoiceDetails
where StockID not in (select StockID from StockMaster);

select distinct GRNNo from SuppInvoiceDetails
where GRNNo not in (select GRNNo from GRNs);

update SuppInvoiceDetails set GRNNo = null
where GRNNo not in (select GRNNo from GRNs);

update SuppInvoiceDetails set GLCode = null
where GLCode not in (select AccountCode from ChartMaster);

alter table SuppInvoiceDetails
add constraint SuppInvoiceDetails_fk_GRNNo
foreign key (GRNNo) references GRNs (GRNNo)
on delete restrict;

alter table SuppInvoiceDetails
add constraint SuppInvoiceDetails_fk_GLCode
foreign key (GLCode) references ChartMaster (AccountCode)
on delete restrict;