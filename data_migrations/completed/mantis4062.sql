begin;
select distinct dt.ID, dt.TransNo, dt.Type, dt.TranDate, e.TranDate
from DebtorTrans dt
join GLTrans e on dt.Type = e.Type and dt.TransNo = e.TypeNo
where dt.TranDate = 0;

update DebtorTrans dt
join GLTrans e on dt.Type = e.Type and dt.TransNo = e.TypeNo
set dt.TranDate = e.TranDate
where dt.TranDate = 0;

commit;