-- check quantities issued and received

select OrderNo, SupplierNo, Initiator, RequisitionNo
from PurchOrders
where SupplierNo not in (
select SupplierID from Suppliers );

select po.OrderNo, po.SupplierNo, po.Initiator, po.RequisitionNo,
wo.WORef, wo.LocCode
from PurchOrders po
join WorksOrders wo
on wo.OrderNo = po.OrderNo
where po.SupplierNo not in (
select SupplierID from Suppliers );

update PurchOrders po
join WorksOrders wo
on wo.OrderNo = po.OrderNo
and wo.WORef = po.RequisitionNo
join Locations loc
on wo.LocCode = loc.LocCode
set po.SupplierNo = loc.SupplierID
where po.SupplierNo not in (
select SupplierID from Suppliers );

select po.OrderNo, po.SupplierNo, po.OrdDate, po.DatePrinted,
pod.ItemDescription, pod.QuantityOrd, pod.QuantityRecd
from PurchOrders po
join PurchOrderDetails pod
on pod.OrderNo = po.OrderNo
where po.SupplierNo not in (
select SupplierID from Suppliers );

-- should delete 12 records
delete from PurchOrders
where DatePrinted is null
and SupplierNo not in (
select SupplierID from Suppliers );

-- fix structure
alter table PurchOrders
add constraint PurchOrders_fk_SupplierNo
foreign key (SupplierNo) references Suppliers (SupplierID)
on delete restrict on update cascade;