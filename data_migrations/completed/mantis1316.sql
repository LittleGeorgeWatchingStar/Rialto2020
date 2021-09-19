-- work orders with two parents
select group_concat(WORef), ParentBuild, count(WORef)
from WorksOrders
where ParentBuild is not null
group by ParentBuild
having count(WORef) > 1;

-- the above, in detail
select * from WorksOrders where ParentBuild = 28240\G

delete from WorksOrders
where WORef = 28242
and ParentBuild = 28240
and UnitsIssued = 0;

alter table WorksOrders
drop key ParentBuild,
add unique key ParentBuild (ParentBuild);

