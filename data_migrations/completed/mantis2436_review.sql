-- items that have no versions at all
select i.StockID, i.Description, i.Discontinued
from StockMaster i
left join ItemVersion v
    on i.StockID = v.stockCode
where (
    i.MBflag = 'M'
    or i.CategoryID = 3 )
and i.Discontinued = 0
group by i.StockID
having count(v.version) = 0;

-- items with invalid auto-build versions
select i.StockID, i.Description, i.CategoryID, i.MBflag, i.AutoBuildVersion
from StockMaster i
left join ItemVersion v
    on i.StockID = v.stockCode
    and i.AutoBuildVersion = v.version
where (
    i.MBflag = 'M'
    or i.CategoryID = 3 )
and i.Discontinued = 0
and (v.stockCode is null or i.AutoBuildVersion = '');

-- items invalid shipping versions
select i.StockID, i.Description, i.CategoryID, i.MBflag, i.ShippingVersion
from StockMaster i
left join ItemVersion v
    on i.StockID = v.stockCode
    and i.ShippingVersion = v.version
where (
    i.MBflag = 'M'
    or i.CategoryID = 3 )
and i.Discontinued = 0
and (v.stockCode is null or i.ShippingVersion = '');

-- items whose only version is the empty string
select i.StockID, i.Description
from StockMaster i
left join ItemVersion v
    on v.stockCode = i.StockID
    and v.version != ''
where (
    i.MBflag = 'M'
    or i.CategoryID = 3 )
and i.Discontinued = 0
group by i.StockID
having count(v.version) = 0;

