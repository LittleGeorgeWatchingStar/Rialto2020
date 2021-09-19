drop table if exists ShipmentProhibition;

create table ShipmentProhibition (
    id serial,
    prohibitedCountry char(2) not null default '',
    stockId varchar(20) null default null,
    categoryId char(6) null default null,
    eccnCode varchar(12) not null default '',
    notes varchar(1000) not null default '',
    primary key (id),
    key (prohibitedCountry),
    constraint prohibitedCountry_stockId_categoryId_eccnCode
    unique key (prohibitedCountry, stockId, categoryId, eccnCode),
    constraint ShipmentProhibition_fk_stockId
    foreign key (stockId) references StockMaster (StockID)
    on delete cascade on update cascade,
    constraint ShipmentProhibition_fk_categoryId
    foreign key (categoryId) references StockCategory (CategoryID)
    on delete cascade on update cascade
)ENGINE=InnoDB DEFAULT CHARSET=utf8;
