alter table products_key_components
add column `type` varchar(50) not null default '' after `name`,
add column quantity int unsigned not null default 0 after `type`;