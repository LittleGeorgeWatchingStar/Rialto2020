DROP TABLE IF EXISTS `StockItemFeature`;
drop table if exists ProductFeature;

create table ProductFeature (
    id varchar(50) not null default '',
    description varchar(255) not null default '',
    `type` varchar(20) not null default '',
    units varchar(10) not null default '',
    primary key (id)
) engine=InnoDB default charset=utf8;

CREATE TABLE `StockItemFeature` (
    `stockItemId` varchar(20) NOT NULL DEFAULT '',
    `productFeatureId` varchar(50) NOT NULL DEFAULT '',
    `value` varchar(100) not null default '',
    PRIMARY KEY (`stockItemId`, `productFeatureId`),
    constraint StockItemFeature_fk_stockItemId
    FOREIGN KEY (`stockItemId`)
    REFERENCES `StockMaster` (`StockID`)
    ON DELETE CASCADE ON UPDATE CASCADE,
    constraint StockItemFeature_fk_productFeatureId
    FOREIGN KEY (`productFeatureId`)
    REFERENCES `ProductFeature` (`id`)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

insert into ProductFeature (id, `type`)
select distinct Feature, `Type`
from ProductFeatures;

insert into StockItemFeature (stockItemId, productFeatureId, `value`)
select distinct StockID, Feature, `Value`
from ProductFeatures;