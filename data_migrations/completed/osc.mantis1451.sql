alter table products_features add column details varchar(255) NOT NULL DEFAULT '' after `value`;

update products_features
set details = `value` where type = 'text';

update products_features
set type = 'choice' where type='text';