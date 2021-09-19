alter table products_features add column type varchar(20) NOT NULL DEFAULT '';
alter table products_features add column `sort_order` SMALLINT not null default 0;