select dt.ID, dt.Type, dt.DebtorNo,
dt.TransNo, dt.OvAmount + dt.OvGST + dt.OvFreight + dt.OvDiscount as totalAmount,
dt.Alloc, ca.Amt
from DebtorTrans dt
left join CustAllocns ca
on dt.ID = ca.TransID_AllocFrom
where dt.Alloc != 0
and dt.Type in (11, 12)
and dt.TranDate >= '2011-01-01'
and ca.ID is null;

select dt.ID, dt.Type, dt.DebtorNo,
dt.TransNo, dt.OvAmount + dt.OvGST + dt.OvFreight + dt.OvDiscount as totalAmount,
dt.Alloc, ca.Amt
from DebtorTrans dt
left join CustAllocns ca
on dt.ID = ca.TransID_AllocTo
where dt.Alloc != 0
and dt.Type in (10, 101)
and dt.TranDate >= '2011-01-01'
and ca.ID is null;


update DebtorTrans dt
left join CustAllocns ca
on dt.ID = ca.TransID_AllocFrom
set dt.Alloc = 0
where dt.Alloc != 0
and dt.Type in (11, 12)
and dt.TranDate >= '2011-01-01'
and ca.ID is null;

update DebtorTrans dt
left join CustAllocns ca
on dt.ID = ca.TransID_AllocTo
set dt.Alloc = 0
where dt.Alloc != 0
and dt.Type in (10, 101)
and dt.TranDate >= '2011-01-01'
and ca.ID is null;