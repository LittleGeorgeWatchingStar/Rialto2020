select bin.StockID,
bin.LocCode,
sum(bin.Quantity) as binQty,
lvl.Quantity as locQty
from StockSerialItems as bin
left join LocStock lvl
on bin.LocCode = lvl.LocCode
and bin.StockID = lvl.StockID
group by bin.LocCode, bin.StockID
having (locQty is null or locQty != binQty)
order by bin.StockID, bin.LocCode;

-- select loc.StockID,
-- loc.LocCode,
-- loc.Quantity as locQty,
-- sum(bin.Quantity) as binQty
-- from LocStock loc
-- join StockSerialItems bin
-- on loc.StockID = bin.StockID
-- and loc.LocCode = bin.LocCode
-- group by bin.LocCode, bin.StockID
-- having locQty != binQty
-- order by bin.StockID, bin.LocCode;
