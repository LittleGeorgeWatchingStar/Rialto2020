alter table PurchData
add column IncrementQty int unsigned not null default 1 after BinSize;

update PurchData p
set p.IncrementQty = (
select min(c.binSize)
from PurchasingCost c
where c.purchasingDataId = p.ID
group by c.purchasingDataId);

show warnings;