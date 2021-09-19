select po.OrderNo, po.RequisitionNo,
pod.ItemDescription, pod.QuantityOrd, pod.QuantityRecd
from PurchOrders po
join PurchOrderDetails pod
on po.OrderNo = pod.OrderNo
where po.Initiator like 'WOSystem'
and po.RequisitionNo not in (
select WORef from WorksOrders);


select po.OrderNo, po.RequisitionNo, po.Initiator, wo.OrderNo, wo.WORef
from PurchOrders po
join WorksOrders wo
on po.OrderNo = wo.OrderNo
where po.RequisitionNo not in (
select WORef from WorksOrders);