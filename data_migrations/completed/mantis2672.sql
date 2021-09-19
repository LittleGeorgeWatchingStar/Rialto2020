-- Type 700 is for converting to controlled
select bad.systemTypeID
, bad.systemTypeNumber
, date(bad.dateMoved)
, bad.stockCode
, bad.locationID
, bad.binID
, bad.quantity
, bad.reference
from StockMove bad
where bad.systemTypeID = 700
order by systemTypeNumber, systemTypeID, locationID, quantity;

-- Recently we used type 17 for converting to controlled
select ok.systemTypeNumber
, date(ok.dateMoved)
, date(bad.dateMoved)
, ok.stockCode
, bad.stockCode
, ok.locationID
, bad.locationID
, ok.binID
, bad.binID
, ok.quantity
, bad.quantity
, ok.reference
from StockMove ok
join StockMove bad
    on ok.systemTypeNumber = bad.systemTypeNumber
where ok.systemTypeID = 17
and bad.systemTypeID = 700;


update StockMove
set systemTypeID = 17
where systemTypeID = 700;