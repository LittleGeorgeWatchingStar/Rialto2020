alter table StockSerialItems
add column manufacturerCode varchar(50) not null default '';

update StockSerialItems bin
join StockSerialMoves ssm
    on bin.SerialNo = ssm.SerialNo
    and bin.StockID = ssm.StockID
join StockMoves sm
    on ssm.StockMoveNo = sm.StkMoveNo
    and ssm.StockID = sm.StockID
join GRNs grn
    on grn.GRNBatch = sm.TransNo
    and grn.ItemCode = sm.StockID
join PurchOrderDetails pod
    on pod.PODetailItem = grn.PODetailItem
    and pod.ItemCode = grn.ItemCode
join PurchData pd
    on pod.PurchDataID = pd.ID
    and pod.ItemCode = pd.StockID
set bin.manufacturerCode = trim(pd.ManufacturerCode)
where sm.Type = 25;
