-- anything with a positive qty received has been received
update LocTransfers
set RecDate = ShipDate
where RecDate is null
and RecQty > 0;

-- anything sent to HQ or product testing is auto-received
update LocTransfers
set RecQty = ShipQty,
    RecDate = ShipDate
where RecDate is null
and RecLoc in (7,13);

-- any transfer whose bin is no longer at that location must have been
-- received and then moved on.
update LocTransfers ti
join StockSerialItems bin
    on ti.SerialNo = bin.SerialNo
set ti.RecQty = ti.ShipQty,
    ti.RecDate = ti.ShipDate
where ti.RecDate is null
and ti.RecLoc != bin.LocCode;

-- Any transfer whose bin has less than the qty transfered must have been
-- received before the stock could be used.
update LocTransfers ti
join StockSerialItems bin
    on ti.SerialNo = bin.SerialNo
set ti.RecQty = ti.ShipQty,
    ti.RecDate = ti.ShipDate
where ti.RecDate is null
and bin.Quantity < ti.ShipQty;

-- Any transfer for an issued work order must have been received.
update LocTransfers ti
join LocTransfersDetail j
    on j.LocTransfersID = ti.Reference
join WorksOrders wo
    on j.WORef = wo.WORef
set ti.RecQty = ti.ShipQty,
    ti.RecDate = ti.ShipDate
where ti.RecDate is null
and wo.UnitsIssued = wo.UnitsReqd;

-- Update transfer header records to match line items.
update LocTransferHeader t
join (
    select Reference, min(RecDate) as RecDate
    from LocTransfers
    where RecDate is not null
    group by Reference )
as ti on t.ID = ti.Reference
set t.DateReceived = ti.RecDate
where t.DateReceived is null
and ti.RecDate is not null;

-- Sync parents with children
update WorksOrders parent
join WorksOrders child
    on child.ParentBuild = parent.WORef
set parent.dateClosed = child.dateClosed
where child.dateClosed is not null
and parent.dateClosed is null;

update WorksOrders parent
join WorksOrders child
    on child.ParentBuild = parent.WORef
set parent.OrderNo = child.OrderNo
where parent.OrderNo is null
and child.OrderNo is not null;