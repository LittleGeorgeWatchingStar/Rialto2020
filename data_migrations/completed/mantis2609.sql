-- Should match corresponding query below.
select wo.WORef,
wo.AccumValueIssued,
sum(e.Amount),
wo.AccumValueIssued - sum(e.Amount) as Diff
from WorksOrders wo
join GLTrans e
    on e.TypeNo = wo.WORef
where e.Type = 28
and e.Account = 12100
group by wo.WORef
having abs(Diff) >= 0.01;

delete from GLTrans where Amount = 0 and Type = 28;

select count(*) from GLTrans where Type = 28;

-- fix GL entries with an exact timestamp match on the
-- corresponding stock moves
update GLTrans e
join WOIssues i
    on e.TypeNo = i.WorkOrderID
    and date(e.TranDate) = date(i.IssueDate)
join StockMove m
    on e.Type = m.systemTypeID
    and i.IssueNo = m.systemTypeNumber
    and e.TranDate = m.dateMoved
set e.TypeNo = i.IssueNo
where e.Type = 28;

-- fix entries whose dates match the corresponding stock moves
update GLTrans e
join WOIssues i
    on e.TypeNo = i.WorkOrderID
    and date(e.TranDate) = date(i.IssueDate)
join StockMove m
    on e.Type = m.systemTypeID
    and i.IssueNo = m.systemTypeNumber
    and date(e.TranDate) = date(m.dateMoved)
set e.TypeNo = i.IssueNo
where e.Type = 28
and e.TypeNo >= 10000;

-- Fix entries whose dates match the corresponding issuance.
update GLTrans e
join WOIssues i
    on e.TypeNo = i.WorkOrderID
    and date(e.TranDate) = date(i.IssueDate)
set e.TypeNo = i.IssueNo
where e.Type = 28
and e.TypeNo >= 10000;

-- Replace work order ID with issuance ID for all remaining enties.
update GLTrans e
join WOIssues i
    on e.TypeNo = i.WorkOrderID
set e.TypeNo = i.IssueNo
where e.Type = 28
and e.TypeNo >= 10000;

-- Ensure everything worked okay.
select * from GLTrans e
where e.Type = 28
and e.TypeNo >= 10000;

select * from GLTrans
where Type = 28 and TypeNo not in (
select IssueNo from WOIssues);

select count(*) from GLTrans where Type = 28;

-- Fix recent issuance reversals to follow the new logic
update GLTrans
set Type = 28,
TypeNo = 3198
where Type = 27
and TypeNo = 101;

update StockMove
set systemTypeID = 28,
systemTypeNumber = 3198
where systemTypeID = 27
and systemTypeNumber = 101;

update GLTrans
set Type = 28,
TypeNo = 3249
where Type = 27
and TypeNo = 102;

update StockMove
set systemTypeID = 28,
systemTypeNumber = 3249
where systemTypeID = 27
and systemTypeNumber = 102;

-- Should be zero
select count(*) from GLTrans where Type = 27;

-- Should match corresponding query above.
select wo.WORef,
wo.AccumValueIssued,
sum(e.Amount),
wo.AccumValueIssued - sum(e.Amount) as Diff
from WorksOrders wo
join WOIssues i
    on i.WorkOrderID = wo.WORef
join GLTrans e
    on e.TypeNo = i.IssueNo
where e.Type = 28
and e.Account = 12100
group by wo.WORef
having abs(Diff) >= 0.01;