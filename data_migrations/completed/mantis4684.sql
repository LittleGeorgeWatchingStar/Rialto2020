select m.id, m.systemTypeID as typeID, m.systemTypeNumber as typeNo
, m.dateMoved , e.TranDate
, m.periodID, e.PeriodNo
, m.reference
from StockMove m
join (
    select distinct Type, TypeNo, TranDate, PeriodNo
    from GLTrans
    where TranDate >= '2014-01-01'
) as e on m.systemTypeID = e.Type and m.systemTypeNumber = e.TypeNo
where m.dateMoved >= '2014-01-01'
and date(e.TranDate) != date(m.dateMoved)
and m.systemTypeID != 28
order by m.id, e.TranDate;


update StockMove m
join (
    select distinct Type, TypeNo, TranDate, PeriodNo
    from GLTrans
    where TranDate >= '2014-01-01'
) as e on m.systemTypeID = e.Type and m.systemTypeNumber = e.TypeNo
set m.dateMoved = e.TranDate
where m.dateMoved >= '2014-01-01'
and date(e.TranDate) != date(m.dateMoved)
and m.systemTypeID != 28;
