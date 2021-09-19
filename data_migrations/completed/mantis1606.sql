CREATE TABLE `DiscountGroup` (
  `id` serial,
  `name` varchar(30) NOT NULL DEFAULT '',
  PRIMARY KEY (`id`),
  UNIQUE KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into DiscountGroup (`name`) select distinct discountGroup from StockDiscount;

ALTER TABLE StockDiscount DROP foreign key `StockDiscount_fk_stockCode`;

CREATE TABLE `StockItemToDiscountGroup` (
  `stockCode` varchar(20) NOT NULL DEFAULT '',
  `discountGroupId` bigint unsigned not null default 0,
  PRIMARY KEY (`stockCode`,`discountGroupId`),
  CONSTRAINT `StockItemToDiscountGroup_fk_discountGroupId`
  FOREIGN KEY (`discountGroupId`) REFERENCES `DiscountGroup` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `StockItemToDiscountGroup_fk_stockCode`
  FOREIGN KEY (`stockCode`) REFERENCES `StockMaster` (`StockID`)
  ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into StockItemToDiscountGroup (`stockCode`, `discountGroupId`)
select `stockCode`, `id`
from StockDiscount osd join DiscountGroup dg
on osd.discountGroup = dg.name;

CREATE TABLE `DiscountRate` (
  `id` serial,
  `discountGroupId` bigint unsigned not null default 0,
  `threshold` int(10) unsigned DEFAULT NULL,
  `discountRate` decimal(5,4) DEFAULT NULL,
  `discountRateRelated` decimal(5,4) DEFAULT NULL,
  UNIQUE KEY (`discountGroupId`, `threshold`),
  CONSTRAINT `DiscountRate_fk_discountGroupId`
  FOREIGN KEY (`discountGroupId`) REFERENCES `DiscountGroup` (`id`)
  ON DELETE CASCADE ON UPDATE CASCADE
);

insert into DiscountRate
(`discountGroupId`, `threshold`, `discountRate`, `discountRateRelated`)
select distinct dg.id, odm.threshold, odm.discountRate, odm.discountRateRelated
from DiscountMatrix odm join DiscountGroup dg
on odm.discountGroup = dg.name;

drop table StockDiscount;
drop table DiscountMatrix;