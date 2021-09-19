alter table SalesReturnItem
add constraint salesReturn_stockItem_workOrder
unique key (salesReturn, stockItem, workOrder);