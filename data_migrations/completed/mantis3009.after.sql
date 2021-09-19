begin;

insert into Requirement
(oldID, consumerType, consumerID, stockCode, version, customizationID, unitQtyNeeded, scrapCount, unitStandardCost)
select
r.id,
'WorkOrder',
q.WorkOrderID,
q.StockID,
q.Version,
q.customizationId,
q.UnitsReq,
q.ScrapCount,
q.StdCost
from StockRequest r
join WORequirements q on r.consumerID = q.ID
where r.consumerType = 'WorkOrderRequirement';

insert into Requirement
(oldID, consumerType, consumerID, stockCode, version, customizationID)
select
r.id,
r.consumerType,
r.consumerID,
i.StockID,
s.Version,
s.CustomizationID
from StockRequest r
join SalesOrderDetails s on r.consumerID = s.ID
join StockMaster i on s.StkCode = i.StockID
where r.consumerType = 'SalesOrderDetail'
and i.MBflag != 'A';

insert into Requirement
(oldID, consumerType, consumerID, stockCode, version, unitQtyNeeded)
select
r.id,
r.consumerType,
r.consumerID,
b.Component,
b.ComponentVersion,
b.Quantity
from StockRequest r
join SalesOrderDetails s on r.consumerID = s.ID
join StockMaster i on s.StkCode = i.StockID
join StockAllocation a on r.id = a.requestID
join BOM b on s.StkCode = b.Parent and b.Component = a.StockID
where r.consumerType = 'SalesOrderDetail'
and i.MBflag = 'A'
group by r.id, a.StockID;

update StockAllocation a
join Requirement r on a.requestID = r.oldID and a.StockID = r.stockCode
set a.requirementID = r.id;

delete from StockAllocation where requirementID = 0;

update StockMove child
join StockMove parent
    on child.parentID = parent.id
set child.parentItem = parent.stockCode;

insert into SalesInvoiceItem
(debtorTransID, orderItemID, qtyInvoiced, unitPrice, taxRate, discountRate)
select
dt.ID,
sod.ID,
-sm.quantity,
sm.unitPrice,
sm.taxRate,
sm.discountRate
from DebtorTrans dt
join SalesOrders so
    on dt.Order_ = so.OrderNo
join SalesOrderDetails sod
    on so.OrderNo = sod.OrderNo
join StockMove sm
    on sm.systemTypeID = dt.`Type`
    and sm.systemTypeNumber = dt.TransNo
where dt.`Type` = 10
and sm.parentID is null
and sm.stockCode = sod.StkCode
order by dt.TranDate;

commit;

alter table StockAllocation add unique key `requirement_source`
(requirementID, SourceType, SourceNo);

alter table Requirement drop column oldID;

rename table WORequirements to WORequirements_archived;
rename table StockRequest to StockRequest_archived;
rename table OrderDeliveryDifferencesLog to OrderDeliveryDifferencesLog_archived;
rename table SalesAnalysis to SalesAnalysis_archived;

