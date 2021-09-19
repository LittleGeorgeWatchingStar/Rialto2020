alter table StockItemFeature drop foreign key `StockItemFeature_fk_productFeatureId`;

CREATE TABLE `NewProductFeature` (
  `id` serial,  -- bigint unsigned not null default 0 auto_increment
  `name` varchar(50) NOT NULL DEFAULT '',
  `description` varchar(255) NOT NULL DEFAULT '',
  `type` varchar(20) NOT NULL DEFAULT '',
  `units` varchar(10) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into NewProductFeature (`name`, `description`, `type`, `units`)
select `id`, `description`, `type`, `units` from ProductFeature;

drop table ProductFeature;

rename table NewProductFeature to ProductFeature;

alter table StockItemFeature drop key `StockItemFeature_fk_productFeatureId`;

truncate table StockItemFeature;

alter table StockItemFeature modify `productFeatureId` bigint unsigned not null default 0;

ALTER TABLE `StockItemFeature`
add constraint StockItemFeature_fk_productFeatureId
FOREIGN KEY (`productFeatureId`)
REFERENCES `ProductFeature`(`id`)
ON DELETE CASCADE ON UPDATE CASCADE;

insert into StockItemFeature (stockItemId, productFeatureId, `value`)
select distinct oldpf.StockID, newpf.id, oldpf.`Value`
from ProductFeatures as oldpf
join ProductFeature as newpf
on oldpf.Feature = newpf.name;