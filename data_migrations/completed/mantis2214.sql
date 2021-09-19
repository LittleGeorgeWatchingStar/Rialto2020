alter table WorksOrders
add column dateCreated timestamp not null default CURRENT_TIMESTAMP after Rework;

update WorksOrders
set dateCreated = ReleasedDate
where dateCreated = 0;

update WorksOrders
set Instructions = ''
where Instructions like 'Board version%';