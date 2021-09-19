alter table WorksOrders
add column dateClosed datetime null default null;

select count(*)
from WorksOrders wo
where (wo.Closed = 1 or wo.UnitsRecd >= wo.UnitsReqd)
and wo.dateClosed is null;

select distinct wo.LocCode, count(wo.WORef), max(wo.WORef)
from WorksOrders wo
where (wo.Closed = 1 or wo.UnitsRecd >= wo.UnitsReqd)
and wo.dateClosed is null
group by wo.LocCode;

select distinct wo.LocCode, count(wo.WORef), max(wo.WORef)
from WorksOrders wo
where (wo.Closed = 1 or wo.UnitsRecd >= wo.UnitsReqd)
and wo.dateClosed is null
and wo.UnitsRecd > 0
group by wo.LocCode;

update WorksOrders wo
join GLTrans gl on gl.TypeNo = wo.WORef and gl.Type = 26
left join GLTrans gl2
on gl2.TypeNo = gl.TypeNo
and gl2.Type = gl.Type
and gl.TranDate < gl2.TranDate
and gl2.TranDate >= wo.ReleasedDate
set wo.dateClosed = gl.TranDate
where (wo.Closed = 1 or wo.UnitsRecd >= wo.UnitsReqd)
and gl.TranDate >= wo.ReleasedDate
and wo.dateClosed is null
and gl2.CounterIndex is null;

update WorksOrders wo
join PurchOrderDetails pod on pod.OrderNo = wo.OrderNo
join GRNs grn on grn.PODetailItem = pod.PODetailItem
left join GRNs grn2
on grn.PODetailItem = grn2.PODetailItem
and grn.DeliveryDate < grn2.DeliveryDate
set dateClosed = grn.DeliveryDate
where (wo.Closed = 1 or wo.UnitsRecd >= wo.UnitsReqd)
and wo.dateClosed is null
and grn2.GRNNo is null
and pod.ItemDescription like 'Labour%';

update WorksOrders wo
join GoodsReceivedNotice grn on grn.PurchaseOrderNo = wo.OrderNo
left join GoodsReceivedNotice grn2
on grn.PurchaseOrderNo = grn2.PurchaseOrderNo
and grn.DeliveryDate < grn2.DeliveryDate
set dateClosed = grn.DeliveryDate
where (wo.Closed = 1 or wo.UnitsRecd >= wo.UnitsReqd)
and wo.dateClosed is null
and grn.DeliveryDate >= wo.ReleasedDate
and grn2.BatchID is null;

update WorksOrders wo
set wo.dateClosed = date_add(wo.ReleasedDate, interval 60 day)
where (wo.Closed = 1 or wo.UnitsRecd >= wo.UnitsReqd)
and wo.dateClosed is null
and wo.ReleasedDate is not null
and wo.ReleasedDate != 0;

update WorksOrders wo
set wo.dateClosed = wo.RequiredBy
where (wo.Closed = 1 or wo.UnitsRecd >= wo.UnitsReqd)
and wo.dateClosed is null
and wo.RequiredBy is not null
and wo.RequiredBy != 0;

update WorksOrders wo
set wo.dateClosed = now()
where (wo.Closed = 1 or wo.UnitsRecd >= wo.UnitsReqd)
and wo.dateClosed is null;