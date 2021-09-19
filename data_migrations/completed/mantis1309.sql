drop table if exists Publication;
create table Publication (
    id serial,
    stockCode varchar(20) not null,
    description varchar(50) not null,
    type varchar(4) not null default '',
    content varchar(255) not null default '',
    public boolean not null default 0,
    primary key(id),
    constraint Publication_fk_stockCode
    foreign key (stockCode) references StockMaster (StockID)
    on delete cascade on update cascade
) engine=InnoDB default charset=utf8;

insert into Publication
(stockCode, description, type, content, public)
select StockID, 'default', 'url', Publication, 1
from StockMaster where Publication != '';
