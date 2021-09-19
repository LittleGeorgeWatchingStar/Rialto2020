alter table StockSerialItems
add column invoiceItemID bigint unsigned null default null;

update StockSerialItems bin
join StockMove move
    on move.binID = bin.SerialNo
join GRNs grn
    on move.systemTypeNumber = grn.GRNBatch
join SuppInvoiceDetails inv
    on grn.invoiceItemID = inv.SIDetailID
set bin.invoiceItemID = inv.SIDetailID
where move.systemTypeID = 25
and move.stockCode = grn.ItemCode
and grn.ItemCode = bin.StockID
and bin.StockID = inv.StockID;

alter table StockSerialItems
add constraint StockSerialItems_fk_invoiceItemID
foreign key (invoiceItemID) references SuppInvoiceDetails (SIDetailID)
on delete restrict;

-- select
-- inv.SIDetailID,
-- group_concat(distinct bin.SerialNo),
-- group_concat(distinct move.id),
-- group_concat(distinct bin.StockID),
-- sum(move.quantity) as TotalQty,
-- inv.Invoicing
-- from StockSerialItems bin
-- join StockMove move
--     on move.binID = bin.SerialNo
-- join GRNs grn
--     on move.systemTypeNumber = grn.GRNBatch
-- join SuppInvoiceDetails inv
--     on bin.invoiceItemID = inv.SIDetailID
-- where move.systemTypeID = 25
-- and move.stockCode = grn.ItemCode
-- and grn.ItemCode = bin.StockID
-- and bin.StockID = inv.StockID
-- group by inv.SIDetailID
-- having TotalQty > inv.Invoicing;
--
-- select move.id, move.dateMoved, move.stockCode, move.systemTypeID, move.quantity, move.unitStandardCost,
-- cost.*
-- from StockMove move
-- join StandardCost cost
--     on move.stockCode = cost.stockCode
-- left join StandardCost nextCost
--     on move.stockCode = nextCost.stockCode
--     and nextCost.startDate > cost.startDate
--     and nextCost.startDate <= move.dateMoved
-- where cost.startDate <= move.dateMoved
-- and (cost.materialCost +
--     cost.labourCost + cost.overheadCost) > 0
-- and nextCost.startDate is null
-- and move.unitStandardCost = 0
-- order by move.id desc
-- limit 100;


update StockMove move
join StandardCost cost
    on move.stockCode = cost.stockCode
left join StandardCost nextCost
    on move.stockCode = nextCost.stockCode
    and nextCost.startDate > cost.startDate
    and nextCost.startDate <= move.dateMoved
set move.unitStandardCost = cost.materialCost +
    cost.labourCost + cost.overheadCost
where cost.startDate <= move.dateMoved
and nextCost.startDate is null
and move.unitStandardCost = 0
and (cost.materialCost +
    cost.labourCost + cost.overheadCost) > 0;