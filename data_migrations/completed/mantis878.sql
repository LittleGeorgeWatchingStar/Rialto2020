drop table if exists SupplierInvoicePattern;

create table SupplierInvoicePattern (
    supplierId bigint unsigned not null default 0,
    keyword varchar(50) not null default '',
    sender varchar(50) not null default '',
    location varchar(30) not null default 'attachment',
    parseDefinition text,
    primary key (supplierId),
    constraint SupplierInvoicePattern_fk_supplierId
    foreign key (supplierId) references Suppliers (SupplierID)
    on delete cascade
);

insert into SupplierInvoicePattern
set supplierId = 1,
keyword =  'Invoice',
sender = 'arrow.com',
location = 'attachment';

insert into SupplierInvoicePattern
set supplierId = 3,
keyword =  'Digi-Key Invoice',
sender = 'digikey.com',
location = 'attachment';

insert into SupplierInvoicePattern
set supplierId = 11,
keyword =  'Invoice',
sender = 'NUHORIZONS.COM',
location = 'attachment';

insert into SupplierInvoicePattern
set supplierId = 14,
keyword =  'PO',
sender = 'innerstep.com',
location = 'attachment';

insert into SupplierInvoicePattern
set supplierId = 19,
keyword =  'UPS Billing',
sender = 'ups.com',
location = 'body link';

insert into SupplierInvoicePattern
set supplierId = 31,
keyword =  'Avnet',
sender = 'avnet.com',
location = 'attachment';

insert into SupplierInvoicePattern
set supplierId = 38,
keyword =  'Invoice',
sender = 'carrferrell.com',
location = 'attachment';

insert into SupplierInvoicePattern
set supplierId = 44,
keyword =  'Invoice',
sender = 'bestekmfg.com',
location = 'attachment';

insert into SupplierInvoicePattern
set supplierId = 54,
keyword =  'E-Invoice',
sender = 'uture.ca',
location = 'attachment';

insert into SupplierInvoicePattern
set supplierId = 61,
keyword =  'Invoice',
sender = '4pcb.com',
location = 'attachment';

insert into SupplierInvoicePattern
set supplierId = 127,
keyword =  'Invoice',
sender = 'ddiglobal.com',
location = 'attachment';

insert into SupplierInvoicePattern
set supplierId = 154,
keyword =  'Invoice',
sender = 'wpgamericas.com',
location = 'attachment';

insert into SupplierInvoicePattern
set supplierId = 160,
keyword =  'Invoice',
sender = 'fastenersuperstore.com',
location = 'attachment';

insert into SupplierInvoicePattern
set supplierId = 162,
keyword =  'Invoice from Sakoman Incorporated',
sender = 'sakoman.com',
location = 'attachment';

insert into SupplierInvoicePattern
set supplierId = 178,
keyword =  'Invoice',
sender = 'circuitco.com',
location = 'attachment';

insert into SupplierInvoicePattern
set supplierId = 182,
keyword =  'Wurth',
sender = 'we-online.com',
location = 'attachment';

insert into SupplierInvoicePattern
set supplierId = 190,
keyword =  'GUM',
sender = 'abracon.com',
location = 'attachment';

insert into SupplierInvoicePattern
set supplierId = 195,
keyword =  'Invoice',
sender = 'L-COM.Com',
location = 'attachment';

insert into SupplierInvoicePattern
set supplierId = 201,
keyword =  'Invoice',
sender = 'labtestcert.com',
location = 'attachment';

insert into SupplierInvoicePattern
set supplierId = 212,
keyword =  'Invoice',
sender = 'marshallelectronics.net',
location = 'attachment';