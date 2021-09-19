select t.SupplierNo, t.Type, t.TransNo,
i.invoiceDate, t.TranDate, e.TranDate as EntryDate,
i.totalCost as InvAmt, t.OvAmount as TranAmt, e.Amount as EntryAmt,
p.PeriodNo as InvPeriod, e.PeriodNo as EntryPeriod
from SupplierInvoice i
join PurchOrders po
    on i.purchaseOrderID = po.OrderNo
join SuppTrans t
    on po.SupplierNo = t.SupplierNo
join GLTrans e
    on t.Type = e.Type
    and t.TransNo = e.TypeNo
join Periods p
    on last_day(i.invoiceDate) = p.LastDate_in_Period
where i.supplierReference = t.SuppReference
and datediff(i.invoiceDate, t.TranDate) != 0
and i.invoiceDate >= '2012-01-01';


update SupplierInvoice i
join PurchOrders po
    on i.purchaseOrderID = po.OrderNo
join SuppTrans t
    on po.SupplierNo = t.SupplierNo
join GLTrans e
    on t.Type = e.Type
    and t.TransNo = e.TypeNo
join Periods p
    on last_day(i.invoiceDate) = p.LastDate_in_Period
set t.TranDate = i.invoiceDate,
    e.TranDate = i.invoiceDate,
    e.PeriodNo = p.PeriodNo
where i.supplierReference = t.SuppReference
and datediff(i.invoiceDate, t.TranDate) != 0
and i.invoiceDate >= '2012-01-01';
