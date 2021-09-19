select i.StockID, i.Description, i.Discontinued, sum(s.Quantity)
from StockMaster i
join LocStock s on i.StockID = s.StockID
where i.Controlled = 0
and i.MBflag in ('M', 'B')
group by i.StockID
having sum(s.Quantity) > 0
order by i.Discontinued asc, i.StockID asc;