alter table CustAllocns
add unique key AllocFrom_AllocTo (TransID_AllocFrom, TransID_AllocTo);

alter table DebtorTrans modify Rate decimal(16,6) not null default 1.0;