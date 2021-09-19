alter table StockItemFeature add column `details` varchar(255) NOT NULL DEFAULT '';

update StockItemFeature sf
join ProductFeature f on sf.productFeatureId = f.id
set sf.details = sf.`value` where f.type = 'text';

update ProductFeature set type='choice' where type='text';