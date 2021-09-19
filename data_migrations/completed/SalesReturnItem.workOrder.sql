delete from SalesReturnItem;
delete from SalesReturn;

alter table SalesReturnItem
add column workOrder int unsigned default null;

alter table SalesReturnItem
add constraint `SalesReturnItem_fk_workOrder`
foreign key (workOrder) references WorksOrders (WORef)
on delete restrict;