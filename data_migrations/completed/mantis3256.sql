alter table SalesOrderDetails
add column chargeForCustomizations boolean not null default 1;

update SalesOrderDetails set chargeForCustomizations = 0;