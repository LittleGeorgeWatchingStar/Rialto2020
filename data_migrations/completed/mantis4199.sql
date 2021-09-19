alter table SalesReturnItem
add column originalStockMoveID bigint unsigned not null default 0 after salesReturn;

select sm.id, sm.stockCode, sm.quantity, sri.id, sri.stockItem, sri.qtyAuthorized, sr.id
from SalesReturnItem sri
join SalesReturn sr on sri.salesReturn = sr.id
join DebtorTrans dt on sr.originalInvoice = dt.ID
join StockMove sm
    on sm.systemTypeID = dt.Type
    and sm.systemTypeNumber = dt.TransNo
where sm.stockCode = sri.stockItem
and (-sm.quantity) >= sri.qtyAuthorized
group by sri.id
order by sr.id, sri.id, sri.stockItem;

create temporary table FixSalesReturnItem
select sm.id as moveID, sri.id as itemID
from SalesReturnItem sri
join SalesReturn sr on sri.salesReturn = sr.id
join DebtorTrans dt on sr.originalInvoice = dt.ID
join StockMove sm
    on sm.systemTypeID = dt.Type
    and sm.systemTypeNumber = dt.TransNo
where sm.stockCode = sri.stockItem
and (-sm.quantity) >= sri.qtyAuthorized
group by sri.id
order by sr.id, sri.id, sri.stockItem;

update SalesReturnItem sri
join FixSalesReturnItem fix on sri.id = fix.itemID
set sri.originalStockMoveID = fix.moveID;

alter table SalesReturnItem
add constraint SalesReturnItem_fk_originalStockMoveID
foreign key (originalStockMoveID) references StockMove (id);

alter table SalesReturnItem
drop foreign key SalesReturnItem_fk_stockItem;
