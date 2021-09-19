-- Find all of the active versions that contain a bag.
create temporary table MoveBags
select v.stockCode, v.version, bom.Component, bom.Quantity,
    target.Component as ShouldBelongTo
from ItemVersion v
join BOM bom
    on bom.Parent = v.stockCode
    and bom.ParentVersion = v.version
join BOM target
    on target.Parent = v.stockCode
    and target.ParentVersion = v.version
join StockMaster targetItem
    on target.Component = targetItem.StockID
where bom.Component like 'BAG%'
and targetItem.CategoryID = 7;

-- Add the bag to all of the child BOMs.
replace into BOM
(Parent, Component, Quantity, ParentVersion)
select distinct m.ShouldBelongTo,
m.Component,
m.Quantity,
v.version
from MoveBags m
join ItemVersion v
    on m.ShouldBelongTo = v.stockCode;

-- Remove the bag from the parent BOMs.
delete bom
from BOM bom
join MoveBags m
    on bom.Parent = m.stockCode
    and bom.ParentVersion = m.version
    and bom.Component = m.Component;