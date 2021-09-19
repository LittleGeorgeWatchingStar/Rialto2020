update StockMove
set quantity = -400
where id = 272794;

insert into StockMove
(systemTypeID, systemTypeNumber, dateMoved, periodID, reference, narrative, stockCode, locationID, binID, quantity, customerID, branchID, unitPrice, discountRate, discountAccountID, unitStandardCost, showOnInvoice, hidden, taxRate, GLTransDR, GLTransCR, parentID, parentItem)
SELECT systemTypeID, systemTypeNumber, dateMoved, periodID, reference, narrative, stockCode, locationID, 14444, quantity, customerID, branchID, unitPrice, discountRate, discountAccountID, unitStandardCost, showOnInvoice, hidden, taxRate, GLTransDR, GLTransCR, parentID, parentItem
from StockMove
where id = 272794;


-- by location
create TEMPORARY TABLE MoveSum
select bin.SerialNo as binId
  , bin.StockID as sku
  , move.locationID
  , loc.LocationName as location
  , ifnull(move.total, 0) as moveQty
  , if(bin.LocCode = move.locationID, bin.Quantity, 0) as binQty
  , if(bin.LocCode = move.locationID, bin.Quantity, 0) - ifnull(move.total, 0) as adjustment
  , move.lastId as lastMoveId
from (
  select binID
  , stockCode
  , locationID
  , sum(quantity) as total
  , max(id) as lastId
  from StockMove
  group by binID, stockCode, locationID
) move
join StockSerialItems bin
  on move.binID = bin.SerialNo
  and move.stockCode = bin.StockID
left join Locations loc
  on move.locationID = loc.LocCode
having moveQty != binQty
order by sku, SerialNo, location;

update SysTypes set TypeNo = TypeNo + 1 where TypeID = 17;

insert into StockMove
(systemTypeID
  , systemTypeNumber
  , dateMoved
  , periodID
  , reference
  , narrative
  , stockCode
  , locationID
  , binID
  , quantity
  , unitStandardCost)
select adj.TypeID
, adj.TypeNo
, '2015-01-01'
, lastMove.periodID
, 'Stock move shims'
, 'Stock move shims'
, lastMove.stockCode
, lastMove.locationID
, lastMove.binID
, movesum.adjustment
, lastMove.unitStandardCost
from MoveSum movesum
join StockMove lastMove on movesum.lastMoveId = lastMove.id
join SysTypes adj
where adj.TypeID = 17
order by lastMove.stockCode;


create TEMPORARY TABLE StockValues
select item.StockID
, item.currentStandardCost
, cost.id as costID
, cat.StockAct
, sum(move.quantity) as totalQty
, (cost.materialCost + cost.labourCost + cost.overheadCost) as unitCost
, sum(move.quantity) * (cost.materialCost + cost.labourCost + cost.overheadCost) as totalValue
from StockMaster item
join StockCategory cat on item.CategoryID = cat.CategoryID
join StockMove move on move.stockCode = item.StockID
join SysTypes adj on adj.TypeID = 17
join StandardCost cost
    on cost.stockCode = item.StockID
    and cost.startDate <= '2015-01-01'
left join StandardCost nextCost
    on nextCost.stockCode = cost.stockCode
    and nextCost.startDate <= '2015-01-01'
    and nextCost.startDate > cost.startDate
where move.systemTypeID = adj.TypeID
and move.systemTypeNumber = adj.TypeNo
and move.dateMoved = '2015-01-01'
and move.reference = 'Stock move shims'
and nextCost.id is null
group by item.StockID;

select adj.TypeID
  , adj.TypeNo
  , '2015-01-01'
  , 143
  , vals.StockAct
  , 'Stock move shims'
  , vals.totalValue
from(
      SELECT StockAct, sum(totalValue) AS totalValue
      FROM StockValues
      GROUP BY StockAct
    ) vals
  join SysTypes adj
where adj.TypeID = 17;

insert into GLTrans
(Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount)
select adj.TypeID
, adj.TypeNo
, '2015-01-01'
, 143
, vals.StockAct
, 'Stock move shims'
, vals.totalValue
from(
    SELECT StockAct, sum(totalValue) AS totalValue
    FROM StockValues
    GROUP BY StockAct
) vals
join SysTypes adj
where adj.TypeID = 17;

insert into GLTrans
(Type, TypeNo, TranDate, PeriodNo, Account, Narrative, Amount)
select adj.TypeID
  , adj.TypeNo
  , '2015-01-01'
  , 143
  , 58500
  , 'Stock move shims'
  , -vals.totalValue
from(
      SELECT StockAct, sum(totalValue) AS totalValue
      FROM StockValues
      GROUP BY StockAct
    ) vals
  join SysTypes adj
where adj.TypeID = 17;
