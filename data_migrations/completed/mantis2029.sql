drop table if exists Product;
create table Product (
    stockId varchar(20) not null default '',
    name varchar(100) not null default '',
    description text not null default '',
    primary key (stockId),
    constraint Product_fk_stockId
    foreign key (stockId) references StockMaster (StockID)
    on delete cascade on update cascade
) engine=InnoDB charset=utf8;

insert into Product
(stockId, name)
select stockItem, fieldValue
from ProductMarketingInfo
where fieldName = 'name';

update Product p
join ProductMarketingInfo m
on p.stockId = m.stockItem
set p.description = m.fieldValue
where m.fieldName = 'description';