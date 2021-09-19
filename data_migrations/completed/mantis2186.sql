select WORef, StockID, Version, Rework
from WorksOrders
where Version = '-any-';

update WorksOrders set Version = '' where Version = '-any-';

insert into ItemVersion (stockId, version)
select distinct wo.StockID, wo.Version
from WorksOrders wo
left join ItemVersion v on wo.StockID = v.stockId and wo.Version = v.version
where v.stockId is null
and wo.Version != '-any-';

alter table WorksOrders
add constraint WorksOrders_fk_ItemVersion
foreign key (StockID, Version)
references ItemVersion (stockId, version)
on delete restrict;