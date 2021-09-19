
alter table GoodsReceivedNotice
add constraint GoodsReceivedNotice_fk_systemTypeID
foreign key (systemTypeID) references SysTypes (TypeID)
on delete restrict;


-- regular WO/POs
begin;
update GLTrans e
join StockProducer wo
  on e.TypeNo = wo.id
join PurchOrders po
  on wo.purchaseOrderID = po.OrderNo
join GoodsReceivedNotice grn
  on grn.PurchaseOrderNo = po.OrderNo
  and abs(timestampdiff(SECOND, grn.DeliveryDate, e.TranDate)) <= 5
set e.Type = 25,
  e.TypeNo = grn.BatchID
where e.Type = 26
and e.TranDate >= '2013-01-01';

update GLTrans e
join StockProducer wo
  on e.TypeNo = wo.id
join PurchOrders po
  on wo.purchaseOrderID = po.OrderNo
join GoodsReceivedNotice grn
  on grn.PurchaseOrderNo = po.OrderNo
  and datediff(grn.DeliveryDate, e.TranDate) = 0
set e.Type = 25,
  e.TypeNo = grn.BatchID
where e.Type = 26
and e.TranDate >= '2013-01-01';

select e.CounterIndex
  , e.Type
  , e.TypeNo
  , e.TranDate
  , wo.id as woID
  , po.OrderNo
  , grn.BatchID
  , grn.DeliveryDate
  , e.Narrative
from GLTrans e
join StockProducer wo
  on e.TypeNo = wo.id
join PurchOrders po
  on wo.purchaseOrderID = po.OrderNo
join GoodsReceivedNotice grn
  on grn.PurchaseOrderNo = po.OrderNo
where e.Type = 26
and e.TranDate >= '2013-01-01'
order by wo.id;



update StockMove e
join StockProducer wo
  on e.systemTypeNumber = wo.id
 and e.stockCode = wo.stockCode
join PurchOrders po
  on wo.purchaseOrderID = po.OrderNo
join GoodsReceivedNotice grn
  on grn.PurchaseOrderNo = po.OrderNo
 and abs(timestampdiff(SECOND, grn.DeliveryDate, e.dateMoved)) <= 5
set e.systemTypeID = 25,
  e.systemTypeNumber = grn.BatchID
where e.systemTypeID = 26
and e.dateMoved >= '2013-01-01';

update StockMove e
join StockProducer wo
  on e.systemTypeNumber = wo.id
 and e.stockCode = wo.stockCode
join PurchOrders po
  on wo.purchaseOrderID = po.OrderNo
join GoodsReceivedNotice grn
  on grn.PurchaseOrderNo = po.OrderNo
 and datediff(grn.DeliveryDate, e.dateMoved) = 0
set e.systemTypeID = 25,
  e.systemTypeNumber = grn.BatchID
where e.systemTypeID = 26
and e.dateMoved >= '2013-01-01';

select e.id
  , e.systemTypeID
  , e.systemTypeNumber
  , e.dateMoved
  , wo.id as woID
  , po.OrderNo
  , grn.BatchID
  , grn.DeliveryDate
  , e.reference
from StockMove e
join StockProducer wo
  on e.systemTypeNumber = wo.id
 and e.stockCode = wo.stockCode
join PurchOrders po
  on wo.purchaseOrderID = po.OrderNo
join GoodsReceivedNotice grn
  on grn.PurchaseOrderNo = po.OrderNo
where e.systemTypeID = 26
and e.dateMoved >= '2013-01-01'
order by wo.id;



update StockMove
set systemTypeNumber = 30511
where id = 269457
and systemTypeNumber = 30508;

update GLTrans
set TypeNo = 30511
where CounterIndex in (548414, 548415)
and TypeNo = 30508;


update StockMove
set systemTypeNumber = 30512
where id = 269462
and systemTypeNumber = 30510;

update GLTrans
set TypeNo = 30512
where CounterIndex in (548421, 548422)
and TypeNo = 30510;


update StockMove
set systemTypeNumber = 30644
where id = 273110
and systemTypeNumber = 30649;

update GLTrans
set TypeNo = 30644
where CounterIndex in (553507, 553508)
and TypeNo = 30649;


select i.grnQty
 , m.moveQty
 , i.stockCode
 , i.PO
from (
    select gi.grnID
     , gi.stockCode
     , sum(gi.qtyReceived) as grnQty
     , grn.PurchaseOrderNo as PO
    from GoodsReceivedItem gi
    join GoodsReceivedNotice grn
      on gi.grnID = grn.BatchID
    where grn.DeliveryDate >= '2013-01-01'
    group by gi.grnID, gi.stockCode
) as i
join (
    select grn.BatchID as grnID
     , sum(m.quantity) as moveQty
     , m.stockCode
     , grn.PurchaseOrderNo as PO
    from StockMove m
    join GoodsReceivedNotice grn
     on m.systemTypeNumber = grn.BatchID
    where m.systemTypeID = 25
    and m.dateMoved >= '2013-01-01'
    group by grn.BatchID, m.stockCode
) as m
  on i.grnID = m.grnID
 and i.stockCode = m.stockCode
where moveQty != grnQty;

commit;