-- Create a new ItemVersion for those items with *something* in the
-- auto build version field
insert into ItemVersion
(stockCode, version, weight)
select i.StockID, i.AutoBuildVersion, i.KGS
from StockMaster i
left join ItemVersion v
    on i.StockID = v.stockCode
    and i.AutoBuildVersion = v.version
where (
    i.MBflag = 'M'
    or i.CategoryID = 3 )
and i.AutoBuildVersion != ''
and v.stockCode is null;

-- bad work order versions
update WorksOrders wo
join ItemVersion v
    on wo.StockID = v.stockCode
join PurchOrderDetails pod
    on wo.OrderNo = pod.OrderNo
    and pod.ItemDescription like 'Labour%'
    and pod.VersionReference like concat('%', wo.StockID, '-R', v.version, '%')
set wo.Version = v.version
where wo.Version = ''
and v.version != '';
