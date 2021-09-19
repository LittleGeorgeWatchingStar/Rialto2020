


-- FirstComp payment  22250
-- convert the payment (22) to a refund (24)
begin;
update GLTrans
set Type = 24,
    TypeNo = (select TypeNo from SysTypes where TypeID = 24)
where Type = 22
and TypeNo = 22250;

update SuppTrans
set Type = 24,
    TransNo = (select TypeNo from SysTypes where TypeID = 24)
where Type = 22
and TransNo = 22250;

update BankTrans
set Type = 24,
    TransNo = (select TypeNo from SysTypes where TypeID = 24)
where Type = 22
and TransNo = 22250;

update SysTypes set TypeNo = TypeNo + 1 where TypeID = 24;

delete from SuppAllocs where TransID_AllocFrom = 7063;
delete from SuppAllocs where TransID_AllocTo = 7063;
update SuppTrans set Settled = 0, Alloc = 0 where ID = 7063;
commit;
