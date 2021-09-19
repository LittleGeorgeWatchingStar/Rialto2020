
-- Fix DebtorTrans => CustBranch relationship --

-- 1) find trans with broken relationships
select t.DebtorNo, t.BranchCode from DebtorTrans t
left join CustBranch b
on t.DebtorNo = b.DebtorNo and t.BranchCode = b.BranchCode
where b.BranchCode is null;
-- 1665 results

-- How many affected transactions belong to customers with just one branch?
select t.ID, t.DebtorNo, b2.DebtorNo, t.BranchCode, b2.BranchCode as Fix
from DebtorTrans t
left join CustBranch b
    on t.DebtorNo = b.DebtorNo and t.BranchCode = b.BranchCode
left join CustBranch b2
    on b2.DebtorNo = t.DebtorNo
where b.BranchCode is null
group by t.ID
having count(b2.BranchCode) = 1;
-- 1171 results

-- Those can be fixed right away
update DebtorTrans t
join (
    select t.DebtorNo, b2.BranchCode as Fix
    from DebtorTrans t
    left join CustBranch b
        on t.DebtorNo = b.DebtorNo and t.BranchCode = b.BranchCode
    left join CustBranch b2
        on b2.DebtorNo = t.DebtorNo
    where b.BranchCode is null
    group by t.ID
    having count(b2.BranchCode) = 1
) as fix
    on t.DebtorNo = fix.DebtorNo
set t.BranchCode = fix.Fix;

-- How many affected transactions have a valid sales order?
select t.ID, t.DebtorNo, t.Order_, o.OrderNo, t.BranchCode, o.BranchCode as Fix
from DebtorTrans t
left join CustBranch b
    on t.DebtorNo = b.DebtorNo and t.BranchCode = b.BranchCode
join SalesOrders o
    on o.OrderNo = t.Order_
join CustBranch b2
    on o.DebtorNo = b2.DebtorNo and o.BranchCode = b2.BranchCode
where b.BranchCode is null;

update DebtorTrans t
join (
    select o.OrderNo, o.BranchCode as Fix
    from DebtorTrans t
    left join CustBranch b
        on t.DebtorNo = b.DebtorNo and t.BranchCode = b.BranchCode
    join SalesOrders o
        on o.OrderNo = t.Order_
    join CustBranch b2
        on o.DebtorNo = b2.DebtorNo and o.BranchCode = b2.BranchCode
    where b.BranchCode is null
) as fix
    on t.Order_ = fix.OrderNo
set t.BranchCode = fix.Fix;

-- Query to fix a specific transaction
select t.TranDate, t.BranchCode, t.Order_, t.Reference, t.OvAmount, t.InvText, b.BranchCode, b.BrName
from DebtorTrans t
join CustBranch b
    on t.DebtorNo = b.DebtorNo
where t.ID = ;


-- 3) prevent this from happening in the future
alter table DebtorTrans
add constraint DebtorTrans_fk_DebtorNo_BranchCode
foreign key (DebtorNo, BranchCode) references CustBranch (DebtorNo, BranchCode)
on delete restrict on update restrict;
