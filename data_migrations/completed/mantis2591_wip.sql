begin;
create temporary table NextYearWaste
select waste.*, receipt.LastReceiptDate
from (
    -- Those wasted in 2013
    select CounterIndex, Type, TypeNo, Amount, TranDate, Narrative from GLTrans
    where TranDate >= '2013-01-01'
    and Type = 26
    and Account = 57200
) as waste
join (
    -- Those with a final receipt in 2012
    select Type, TypeNo, max(TranDate) as LastReceiptDate from GLTrans
    where Type = 26
    and Account = 12500
    group by Type, TypeNo
    having date(max(TranDate)) between '2012-01-01' and '2012-12-31'
) as receipt
    on waste.TypeNo = receipt.TypeNo;

update GLTrans e
join NextYearWaste w
    on e.Type = w.Type
    and e.TypeNo = w.TypeNo
    and e.TranDate = w.TranDate
set e.TranDate = '2012-12-31',
    e.PeriodNo = 118
where e.Account in (12100, 57200);
commit;



