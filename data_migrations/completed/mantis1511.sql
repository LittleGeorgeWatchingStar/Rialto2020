alter table WORequirements
modify column Version varchar(31) not null default '';

select wo.StockID as parent, wo.Version as pVersion,
wor.StockID as component, wor.Version as cVersion
from WorksOrders wo
join WORequirements wor on wor.WorkOrderID = wo.WORef
where wo.Closed = 0
and wor.Version = '-any-';


update WorksOrders wo
join WORequirements wor on wor.WorkOrderID = wo.WORef
join StockMaster i on wor.StockID = i.StockID
set wor.Version = i.AutoBuildVersion
where wo.Closed = 0
and wor.Version = '-any-';