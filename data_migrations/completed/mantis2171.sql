create table StandardCost (
    id serial,
    stockCode varchar(20) not null default '',
    materialCost decimal(20,4) not null default 0.0,
    labourCost decimal(20,4) not null default 0.0,
    overheadCost decimal(20,4) not null default 0.0,
    previousCost decimal(20,4) null null,
    startDate datetime,
    primary key(id),
    constraint StandardCost_fk_stockCode
    foreign key (stockCode) references StockMaster (StockID)
    on delete restrict on update cascade
) engine=innodb charset=utf8;

insert into StandardCost
(id, stockCode, previousCost, materialCost, startDate)
SELECT
TypeNo,
trim(SUBSTRING(
    Narrative,
    1,
    LOCATE(' ', Narrative)
)) as StockID,
SUBSTRING(
    Narrative,
    LOCATE('was ', Narrative) + 4,
    LOCATE(' changed', Narrative) - LOCATE('was ', Narrative) - 4
) as PreviousCost,
SUBSTRING(
    Narrative,
    LOCATE(' changed to ', Narrative) + 12,
    LOCATE(' x ', Narrative) - LOCATE(' changed to ', Narrative) - 12
) as materialCost,
TranDate
FROM GLTrans
WHERE Type = 35
AND Account = 58500
ORDER BY TranDate, TypeNo ASC;

insert into StandardCost
(stockCode, materialCost, labourCost, overheadCost, startDate)
select StockID, Materialcost, Labourcost, Overheadcost, '2003-01-01'
from StockMaster
where StockID not in (select stockCode from StandardCost);


alter table StockMaster
add column currentStandardCost bigint unsigned null default null,
add constraint StockMaster_fk_currentStandardCost
foreign key (currentStandardCost) references StandardCost (id)
on delete restrict;