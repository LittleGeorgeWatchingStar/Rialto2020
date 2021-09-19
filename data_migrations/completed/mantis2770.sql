replace into StockItemAttribute
(stockCode, attribute, value)
select distinct StockID, 'bag', ''
from StockMaster
where StockID like 'BAG%';


replace into StockItemAttribute
(stockCode, attribute, value)
select distinct StockID, 'box', ''
from StockMaster
where StockID like 'BOX%';