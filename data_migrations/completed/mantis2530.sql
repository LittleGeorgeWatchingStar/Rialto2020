
-- Create inactive versions for any that are found in the requirements
-- but missing from ItemVersion
insert into ItemVersion
(stockCode, version, active, weight)
select distinct req.StockID, req.Version, 0, i.KGS
from WORequirements req
join StockMaster i
    on i.StockID = req.StockID
left join ItemVersion v
    on v.stockCode = req.StockID
    and v.version = req.Version
where v.version is null
and req.Version != '-any-';

-- Make a list of items that only have one version
create temporary table OneVersion
select stockCode, version
from ItemVersion
group by stockCode
having count(version) = 1;

-- Update requirements for items that only have one version
update WORequirements req
join OneVersion v
    on req.StockID = v.stockCode
set req.Version = v.version
where req.Version = '-any-';

-- Update requirements based on the parent BOM
update WORequirements req
join WorksOrders wo
    on req.WorkOrderID = wo.WORef
join BOM b
    on b.Parent = wo.StockID
    and b.ParentVersion = wo.Version
    and b.Component = req.StockID
set req.Version = b.ComponentVersion
where req.Version = '-any-'
and b.ComponentVersion != '-any-';

-- Update requirements from the auto-build version
update WORequirements req
join StockMaster i
    on i.StockID = req.StockID
set req.Version = i.AutoBuildVersion
where req.Version = '-any-';

-- Should be empty set
select req.StockID, req.Version
from WORequirements req
left join ItemVersion v
    on v.stockCode = req.StockID
    and v.version = req.Version
where v.version is null;

-- Update WORequirements constraints
alter table WORequirements
add constraint WORequirements_fk_ItemVersion
foreign key (StockID, Version) references ItemVersion (stockCode, version)
on delete restrict on update restrict;