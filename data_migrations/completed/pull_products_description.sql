replace into erp_dev.ProductMarketingInfo
(stockItem, fieldName, fieldValue)
select distinct p.products_model,
'overview',
d.products_description
from osc_dev.products p
join osc_dev.products_description d
on p.products_id = d.products_id
join erp_dev.StockMaster sm
on p.products_model = sm.StockID
where d.language_id = 1;