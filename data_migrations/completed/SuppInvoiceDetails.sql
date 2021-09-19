select i.PONumber, i.StockID, d.ItemCode, sm.Package, i.Description
from SuppInvoiceDetails i
join PurchOrders po on i.PONumber = po.OrderNo
join PurchOrderDetails d on po.OrderNo = d.OrderNo
left join StockMaster sm on d.ItemCode = sm.StockID
where i.StockID not in (select StockID from StockMaster)
and i.StockID != ''
order by i.StockID, d.ItemCode;

alter table SuppInvoiceDetails
modify column StockID varchar(31) null default null;

update SuppInvoiceDetails
set StockID = null where StockID = '';

update SuppInvoiceDetails
set StockID = 'L021' where StockID = 'TEST_L021_SUB';

update SuppInvoiceDetails
set StockID = 'CBL0006' where StockID = 'CBL006';

update SuppInvoiceDetails
set StockID = 'CBL0004' where StockID = 'CBL004';

update SuppInvoiceDetails
set StockID = 'CBL0001' where StockID = 'CBL001';

update SuppInvoiceDetails
set StockID = 'CC681A' where StockID = 'C681A';

update SuppInvoiceDetails
set StockID = 'ICL232' where StockID = 'FT232RQ';

update SuppInvoiceDetails
set StockID = 'ICL146' where StockID = 'ICL146A';

update SuppInvoiceDetails
set StockID = 'L030N' where StockID = 'L030M';

select i.PONumber, i.StockID
from SuppInvoiceDetails i
where i.StockID not in (select StockID from StockMaster);

update SuppInvoiceDetails
set StockID = null where StockID not in (select StockID from StockMaster);

alter table SuppInvoiceDetails
add constraint SuppInvoiceDetails_fk_StockID
foreign key (StockID) references StockMaster (StockID)
on delete restrict;

