select d.ID, d.OrderNo, d.StkCode, d.UnitPrice, t.Order_, m.StockID, m.Price
from SalesOrderDetails d
join DebtorTrans t on d.OrderNo = t.Order_
join StockMoves m on t.Type = m.Type and t.TransNo = m.TransNo
where d.StkCode not in ( select StockID from StockMaster )
and m.Price = d.UnitPrice
and m.StockID not in ( select dd.StkCode from SalesOrderDetails dd where dd.OrderNo = t.Order_ )
and t.`Type` = 10
order by d.OrderNo, m.StockID;

update SalesOrderDetails
set StkCode = 'GS400G01'
where ID in (12951, 13028)
and StkCode = 'GS400G';

update SalesOrderDetails
set StkCode = 'CAS00035'
where StkCode = 'CAS00004'
and ID = 13915;

update SalesOrderDetails
set StkCode = 'MMC1024'
where StkCode = '';

select OrderNo, StkCode, Quantity, QtyInvoiced, ActualDispatchDate, Completed
from SalesOrderDetails where StkCode not in (select StockID from StockMaster);

alter table SalesOrderDetails
add constraint SalesOrderDetails_fk_StkCode
foreign key (StkCode) references StockMaster (StockID)
on delete restrict on update cascade;

show create table SalesOrderDetails\G