alter table ProductFeature add `category` varchar(50) NOT NULL DEFAULT '';
alter table ProductFeature drop index `name`;
alter table ProductFeature add unique key `name_category` (`name`, `category`);