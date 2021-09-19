-- DOUBLE-CHECK THAT ALL ISSUE ITEMS HAVE A PARENT

select distinct IssueID from WOIssueItems where IssueID not in
(select IssueNo from WOIssues);


-- CHECK THAT ALL COMPONENTS ARE VALID ITEMS
select distinct ii.StockID as Component, wo.StockID as Parent, wo.Version
from WOIssueItems ii
left join WOIssues i on ii.IssueID = i.IssueNo
left join WorksOrders wo on i.WorkOrderID = wo.WORef
where ii.StockID not in
(select StockID from StockMaster)
order by Component, Parent;

select distinct r.StockID as Component, wo.StockID as Parent, wo.Version
from WORequirements r
left join WorksOrders wo on r.WorkOrderID = wo.WORef
where r.StockID not in
(select StockID from StockMaster)
order by Component, Parent;

-- RECOVERING ISSUED WORK ORDERS IS CRITICAL

select distinct WorkOrderID from WOIssues where WorkOrderID not in
(select WORef from WorksOrders);

select distinct TypeNo, Narrative, Amount from GLTrans
where Type = 28 and TypeNo not in
(select WORef from WorksOrders);

select i.WorkOrderID, ii.IssueID, ii.StockID
from WOIssueItems ii
join WOIssues i on ii.IssueID = i.IssueNo
where i.WorkOrderID not in (select WORef from WorksOrders)
group by i.IssueNo;

select m.StockID, m.Qty, m.LocCode from StockMoves m
join WOIssues i on m.Type = 28 and m.TransNo = i.IssueNo
where i.WorkOrderID not in (select WORef from WorksOrders);


-- ONE WORK ORDER IS OVER-ISSUED
select distinct TypeNo, TranDate, Narrative, Amount from GLTrans
where Type = 28 and TypeNo = 25712;

select StockID, TransNo, LocCode, TranDate, Qty from StockMoves
where Type = 28 and TransNo in (1314, 1315);

-- 1314 is attributed to the wrong wo
select StockID from WOIssueItems where IssueID = 1314;
select StockID from WOIssueItems where IssueID = 1315;

select WORef, StockID, Version, LocCode, UnitsReqd, UnitsIssued, UnitsRecd
from WorksOrders
where UnitsIssued >= 120
and LocCode = 8
and WORef not in (select WorkOrderID from WOIssues);

select StockID, UnitsReq from WORequirements where WorkOrderID = 22411 order by StockID;
select StockID, (QtyIssued / 120) as UnitQtyIssued from WOIssueItems where IssueID = 1314 order by StockID;



