alter table SuppInvoiceDetails
add column harmonizationCode varchar(20) not null default '',
add column eccnCode varchar(12) not null default '',
add column countryOfOrigin char(2) not null default '',
add column leadStatus varchar(20) not null default '',
add column rohsStatus varchar(20) not null default '',
add column reachStatus varchar(40) not null default '',
add column reachDate date null default null;
