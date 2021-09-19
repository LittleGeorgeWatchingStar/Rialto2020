select OrderNo, StkCode from SalesOrderDetails where StkCode not in ( select StockID from StockMaster );
update SalesOrderDetails set StkCode = 'GUM270B-XM4-BT' where StkCode = 'GUM270B-XM4-';
select count(ID) as qty from SalesOrderDetails group by OrderNo, StkCode order by qty desc limit 5;
ALTER TABLE SalesOrderDetails ADD UNIQUE KEY OrderNo_StkCode (OrderNo, StkCode);
