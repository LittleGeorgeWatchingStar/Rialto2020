alter table SalesReturnItem
add column qtyPassed int unsigned not null default 0
after qtyReceived;

alter table SalesReturnItem
add column qtyFailed int unsigned not null default 0
after qtyPassed;
