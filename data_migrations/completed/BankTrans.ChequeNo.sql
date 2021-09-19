-- investigate and test queries
select count(distinct bt.BankTransID)
from BankTrans bt
join SuppTrans st
on bt.Type = st.Type and bt.TransNo = st.SuppReference
where bt.Type = 22
and bt.BankTransType = 'Cheque';

-- add a column for check number
alter table BankTrans
add column ChequeNo int unsigned not null default 0;

-- fill in the check number
update BankTrans bt
join SuppTrans st
on bt.Type = st.Type and bt.TransNo = st.SuppReference
set bt.ChequeNo = bt.TransNo
where bt.Type = 22
and bt.BankTransType = 'Cheque';

-- sanity checks
select count(distinct bt.BankTransID)
from BankTrans bt
where bt.TransNo = bt.ChequeNo;

select bt.Type, bt.TransNo, bt.Ref, bt.BankTransType,
bt.ChequeNo, st.TransNo
from BankTrans bt
join SuppTrans st
on bt.Type = st.Type and bt.TransNo = st.SuppReference
where bt.Type = 22
and bt.BankTransType = 'Cheque'
and st.TransNo > 0
order by bt.BankTransID desc
limit 100;

-- then fix the trans number
update BankTrans bt
join SuppTrans st
on bt.Type = st.Type and bt.TransNo = st.SuppReference
set bt.TransNo = st.TransNo
where bt.Type = 22
and bt.BankTransType = 'Cheque';

-- more sanity checks
select bt.Type, bt.TransNo, bt.Ref, bt.BankTransType,
bt.ChequeNo, st.TransNo
from BankTrans bt
join SuppTrans st
on bt.Type = st.Type and bt.ChequeNo = st.SuppReference
where bt.Type = 22
and bt.BankTransType = 'Cheque'
and st.TransNo > 0
order by bt.BankTransID desc
limit 100;