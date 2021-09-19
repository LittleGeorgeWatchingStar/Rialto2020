alter table SalesOrders add column SalesStage varchar(30);
update SalesOrders set SalesStage = 'order' where quotation = 0;
update SalesOrders set SalesStage = 'quotation' where quotation = 1;

alter table SalesOrders drop column quotation;