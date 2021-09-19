select t.* from LocTransfers t
join StockMaster i on t.StockID = i.StockID
where i.Controlled = 1
and t.SerialNo is null
and t.RecQty < t.ShipQty
and t.ShipDate >= '2012-01-01';



select t.*, ssm.SerialNo, move.Qty
from LocTransfers t
join StockMaster i on t.StockID = i.StockID
join StockMoves move
    on move.StockID = t.StockID
    and move.LocCode = t.RecLoc
join StockSerialMoves ssm
    on ssm.StockMoveNo = move.StkMoveNo
where i.Controlled = 1
and t.SerialNo is null
and t.RecQty < t.ShipQty
and t.RecLoc != 7
and move.Reference like 'Convert %';


update LocTransfers t
join StockMaster i on t.StockID = i.StockID
join StockMoves move
    on move.StockID = t.StockID
    and move.LocCode = t.RecLoc
join StockSerialMoves ssm
    on ssm.StockMoveNo = move.StkMoveNo
set t.SerialNo = ssm.SerialNo
where i.Controlled = 1
and t.SerialNo is null
and t.RecQty < t.ShipQty
and t.RecLoc != 7
and move.Reference like 'Convert %'
