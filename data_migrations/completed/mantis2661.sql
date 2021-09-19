-- make sure SysTypes.TypeNo is up-to-date for Type = 24
update SysTypes
set TypeNo = (select max(TypeNo) from GLTrans where Type = 24) + 1
where TypeID = 24;



-- Invoice 27440
begin;
-- convert the invoice (20) to a debit note (21)
update GLTrans
set Type = 21,
    TypeNo = (select TypeNo from SysTypes where TypeID = 21)
where Type = 20
and TypeNo = 27440;

update SuppTrans
set Type = 21,
    TransNo = (select TypeNo from SysTypes where TypeID = 21)
where Type = 20
and TransNo = 27440;

update BankTrans
set Type = 21,
    TransNo = (select TypeNo from SysTypes where TypeID = 21)
where Type = 20
and TransNo = 27440;

update SysTypes set TypeNo = TypeNo + 1 where TypeID = 21;

-- convert the payment (22) to a refund (24)
update GLTrans
set Type = 24,
    TypeNo = (select TypeNo from SysTypes where TypeID = 24)
where Type = 22
and TypeNo = 24270;

update SuppTrans
set Type = 24,
    TransNo = (select TypeNo from SysTypes where TypeID = 24)
where Type = 22
and TransNo = 24270;

update BankTrans
set Type = 24,
    TransNo = (select TypeNo from SysTypes where TypeID = 24)
where Type = 22
and TransNo = 24270;

update SysTypes set TypeNo = TypeNo + 1 where TypeID = 24;

delete from SuppAllocs where TransID_AllocFrom in (12794,12795);
delete from SuppAllocs where TransID_AllocTo in (12794,12795);
update SuppTrans set Settled = 0, Alloc = 0 where ID in (12794,12795);
commit;



-- Invoice 22102
begin;
-- convert the invoice (20) to a debit note (21)
update GLTrans
set Type = 21,
    TypeNo = (select TypeNo from SysTypes where TypeID = 21)
where Type = 20
and TypeNo = 22102;

update SuppTrans
set Type = 21,
    TransNo = (select TypeNo from SysTypes where TypeID = 21)
where Type = 20
and TransNo = 22102;

update BankTrans
set Type = 21,
    TransNo = (select TypeNo from SysTypes where TypeID = 21)
where Type = 20
and TransNo = 22102;

update SysTypes set TypeNo = TypeNo + 1 where TypeID = 21;

-- convert the payment (22) to a refund (24)
update GLTrans
set Type = 24,
    TypeNo = (select TypeNo from SysTypes where TypeID = 24)
where Type = 22
and TypeNo =  21189;

update SuppTrans
set Type = 24,
    TransNo = (select TypeNo from SysTypes where TypeID = 24)
where Type = 22
and TransNo =  21189;

update BankTrans
set Type = 24,
    TransNo = (select TypeNo from SysTypes where TypeID = 24)
where Type = 22
and TransNo =  21189;

update SysTypes set TypeNo = TypeNo + 1 where TypeID = 24;

delete from SuppAllocs where TransID_AllocFrom in (4235, 4236);
delete from SuppAllocs where TransID_AllocTo in (4235, 4236);
update SuppTrans set Settled = 0, Alloc = 0 where ID in (4235, 4236);
commit;



-- Invoice 23689
begin;
-- convert the invoice (20) to a debit note (21)
update GLTrans
set Type = 21,
    TypeNo = (select TypeNo from SysTypes where TypeID = 21)
where Type = 20
and TypeNo = 23689;

update SuppTrans
set Type = 21,
    TransNo = (select TypeNo from SysTypes where TypeID = 21)
where Type = 20
and TransNo = 23689;

update BankTrans
set Type = 21,
    TransNo = (select TypeNo from SysTypes where TypeID = 21)
where Type = 20
and TransNo = 23689;

update SysTypes set TypeNo = TypeNo + 1 where TypeID = 21;

-- convert the payment (22) to a refund (24)
update GLTrans
set Type = 24,
    TypeNo = (select TypeNo from SysTypes where TypeID = 24)
where Type = 22
and TypeNo =  22135;

update SuppTrans
set Type = 24,
    TransNo = (select TypeNo from SysTypes where TypeID = 24)
where Type = 22
and TransNo =  22135;

update BankTrans
set Type = 24,
    TransNo = (select TypeNo from SysTypes where TypeID = 24)
where Type = 22
and TransNo =  22135;

update SysTypes set TypeNo = TypeNo + 1 where TypeID = 24;

delete from SuppAllocs where TransID_AllocFrom in (6788, 6789);
delete from SuppAllocs where TransID_AllocTo in (6788, 6789);
update SuppTrans set Settled = 0, Alloc = 0 where ID in (6788, 6789);
commit;



-- Invoice 25561 from Pivotal
begin;
-- convert the invoice (20) to a debit note (21)
update GLTrans
set Type = 21,
    TypeNo = (select TypeNo from SysTypes where TypeID = 21)
where Type = 20
and TypeNo = 25561;

update SuppTrans
set Type = 21,
    TransNo = (select TypeNo from SysTypes where TypeID = 21)
where Type = 20
and TransNo = 25561;

update BankTrans
set Type = 21,
    TransNo = (select TypeNo from SysTypes where TypeID = 21)
where Type = 20
and TransNo = 25561;

update SysTypes set TypeNo = TypeNo + 1 where TypeID = 21;

-- convert the payment (22) to a refund (24)
update GLTrans
set Type = 24,
    TypeNo = (select TypeNo from SysTypes where TypeID = 24)
where Type = 22
and TypeNo =  23326;

update SuppTrans
set Type = 24,
    TransNo = (select TypeNo from SysTypes where TypeID = 24)
where Type = 22
and TransNo =  23326;

update BankTrans
set Type = 24,
    TransNo = (select TypeNo from SysTypes where TypeID = 24)
where Type = 22
and TransNo =  23326;

update SysTypes set TypeNo = TypeNo + 1 where TypeID = 24;

delete from SuppAllocs where TransID_AllocFrom in (9870, 10163);
delete from SuppAllocs where TransID_AllocTo in (9870, 10163);
update SuppTrans set Settled = 0, Alloc = 0 where ID in (9870, 10163);
commit;