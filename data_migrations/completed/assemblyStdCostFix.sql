-- This query is WRONG.  Use the one below.
select inv.TransNo, sod.OrderNo, sod.StkCode as StockID,
    sod.QtyInvoiced as Qty,
    inv.Prd, gl.Account, gl.Amount, gl.Narrative
from SalesOrderDetails sod
join SalesOrders so
    on sod.OrderNo = so.OrderNo
join StockMaster item
    on sod.StkCode = item.StockID
join DebtorTrans inv
    on inv.Order_ = sod.OrderNo
left join GLTrans gl
    on gl.Type = inv.Type
    and gl.TypeNo = inv.TransNo
    and gl.Account = 50000
    and gl.Narrative like concat('%', item.StockID, '%')
where sod.QtyInvoiced > 0
and inv.Type = 10
and item.MBflag = 'A'
and so.OrdDate >= '2011-01-01 00:00:00'
and gl.Account is null
order by TransNo asc, OrderNo asc, StockID asc;

-- This query finds the records affected by the bug.
select inv.TransNo, inv.Order_ as OrderNo, move.StockID,
    -move.Qty as Qty, inv.Prd, gl.Account, gl.Amount,
    gl.Narrative
from DebtorTrans inv
join StockMoves move
    on inv.Type = move.Type
    and inv.TransNo = move.TransNo
join StockMaster item
    on move.StockID = item.StockID
left join GLTrans gl
    on gl.Type = inv.Type
    and gl.TypeNo = inv.TransNo
    and gl.Account = 50000
    and gl.Narrative like concat('%', item.StockID, '%')
where inv.Type = 10
and item.MBflag = 'A'
and inv.TranDate >= '2011-01-01 00:00:00'
and gl.Account is null
order by TransNo asc, OrderNo asc, StockID asc;