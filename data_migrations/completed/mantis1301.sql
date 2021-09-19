alter table SalesReturnItem
change column workOrder originalWorkOrder bigint unsigned null default null,
add column reworkOrder bigint unsigned null default null,
drop foreign key SalesReturnItem_fk_workOrder,
add constraint SalesReturnItem_fk_originalWorkOrder
foreign key (originalWorkOrder) references WorksOrders (WORef)
on delete restrict,
add constraint SalesReturnItem_fk_reworkOrder
foreign key (reworkOrder) references WorksOrders (WORef)
on delete set null;