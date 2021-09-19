select * from GLTrans
where PeriodNo >= 107
and Account = 11000
and Type = 11
and Narrative like 'Bank transfer fee for sales order %';

update GLTrans
set Account = 22000
where PeriodNo >= 107
and Account = 11000
and Type = 11
and Narrative like 'Bank transfer fee for sales order %';