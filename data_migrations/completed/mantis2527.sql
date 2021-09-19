insert into ItemVersion
(stockCode, version, active, weight)
select i.StockID, '', 1, i.KGS
from StockMaster i
left join ItemVersion v
    on i.StockID = v.stockCode
where v.version is null;
