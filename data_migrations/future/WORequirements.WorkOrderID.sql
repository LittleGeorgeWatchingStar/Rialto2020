-- ASSESS

select distinct WorkOrderID from WORequirements where WorkOrderID not in
(select WORef from WorksOrders);


-- DESTRUCTIVE REPAIRS

delete from WORequirements where WorkOrderID not in
(select WORef from WorksOrders);

alter table WORequirements
add constraint WORequirements_fk_WorkOrderID
foreign key (WorkOrderID) references WorksOrders (WORef)
on delete cascade;