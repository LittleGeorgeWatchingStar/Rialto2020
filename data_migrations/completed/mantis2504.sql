select e.TranDate as CrDate,
substring(e.Narrative, 1, 12) as CrNarrative,
e.Account as CrAccount,
e.Amount as CrAmount,
credit.Order_ as OrderNo,
substring(credit.InvText, 1, 12) as CreditMemo,
ie.TranDate as InvDate,
substring(ie.Narrative, 1, 12) as InvNarrative,
ie.Account as InvAccount,
ie.Amount as InvAmount
from GLTrans e
join DebtorTrans credit
    on e.Type = credit.Type
    and e.TypeNo = credit.TransNo
join SalesOrders o
    on credit.Order_ = o.OrderNo
join DebtorTrans invoice
    on invoice.Order_ = o.OrderNo
join GLTrans ie
    on invoice.Type = ie.Type
    and invoice.TransNo = ie.TypeNo
where e.Type = 11
and credit.Reference like 'Inv-%'
and e.Account = 11000
and e.TranDate >= '2012-01-01'
and ie.Account = 22000
order by OrderNo;

select credit.TranDate as CrDate,
substring(credit.Narrative, 1, 15) as CrNarrative,
credit.Account as CrAccount,
credit.Amount as CrAmount,
crTrans.Order_ as OrderNo,
o.OrderType,
substring(crTrans.InvText, 1, 15) as CreditMemo,
invoice.TranDate as InvDate,
substring(invoice.Narrative, 1, 15) as InvNarrative,
invoice.Account as InvAccount,
invoice.Amount as InvAmount
from GLTrans credit
join DebtorTrans crTrans
    on credit.Type = crTrans.Type
    and credit.TypeNo = crTrans.TransNo
join SalesOrders o
    on crTrans.Order_ = o.OrderNo
join DebtorTrans invTrans
    on invTrans.Order_ = o.OrderNo
join GLTrans invoice
    on invTrans.Type = invoice.Type
    and invTrans.TransNo = invoice.TypeNo
where credit.Type = 12
and credit.Account = 22000
and credit.Amount != 0
and credit.TranDate >= '2012-01-01'
and credit.TranDate <= invoice.TranDate
and invoice.Account = 11000
and invoice.Type = 10
and invoice.Amount != 0
and abs(credit.Amount) = abs(invoice.Amount)
order by OrderNo;


update GLTrans e
join DebtorTrans credit
    on e.Type = credit.Type
    and e.TypeNo = credit.TransNo
join SalesOrders o
    on credit.Order_ = o.OrderNo
join DebtorTrans invoice
    on invoice.Order_ = o.OrderNo
join GLTrans ie
    on invoice.Type = ie.Type
    and invoice.TransNo = ie.TypeNo
set e.Account = 22000
where e.Type = 11
and credit.Reference like 'Inv-%'
and e.Account = 11000
and e.TranDate >= '2012-01-01'
and ie.Account = 22000;


update GLTrans credit
join DebtorTrans crTrans
    on credit.Type = crTrans.Type
    and credit.TypeNo = crTrans.TransNo
join SalesOrders o
    on crTrans.Order_ = o.OrderNo
join DebtorTrans invTrans
    on invTrans.Order_ = o.OrderNo
join GLTrans invoice
    on invTrans.Type = invoice.Type
    and invTrans.TransNo = invoice.TypeNo
set invoice.Account = 22000
where credit.Type = 12
and credit.Account = 22000
and credit.Amount != 0
and credit.TranDate >= '2012-01-01'
and credit.TranDate <= invoice.TranDate
and invoice.Account = 11000
and invoice.Type = 10
and invoice.Amount != 0
and abs(credit.Amount) = abs(invoice.Amount);


alter table GLTrans modify column TranDate datetime not null;