select i.StockID, i.Description
from StockMaster i

left join StockMoves sm
    on sm.StockID = i.StockID
left join StockSerialItems bin
    on bin.StockID = i.StockID
left join ItemVersion iv
    on iv.stockCode = i.StockID
left join PurchOrderDetails pod
    on pod.ItemCode = i.StockID
left join PurchData pd
    on pd.StockID = i.StockID
left join WorksOrders wo
    on wo.StockID = i.StockID
left join WORequirements wor
    on wor.StockID = i.StockID
left join BOM bom
    on bom.Component = i.StockID
left join BOM bom2
    on bom2.Parent = i.StockID
left join Substitutions sub
    on sub.ComponentID = i.StockID
left join Substitutions sub2
    on sub2.SubstituteID = i.StockID
left join SalesOrderDetails sod
    on sod.StkCode = i.StockID

where sm.StkMoveNo is null
and bin.SerialNo is null
and iv.version is null
and pod.PODetailItem is null
and pd.ID is null
and wo.WORef is null
and wor.ID is null
and bom.ID is null
and bom2.ID is null
and sub.ID is null
and sub2.ID is null
and sod.ID is null;