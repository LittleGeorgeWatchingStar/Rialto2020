drop table if exists PurchasingCostTemplate;
drop table if exists PurchasingDataTemplate;
create table PurchasingDataTemplate (
    id serial,
    strategy varchar(50) not null default '',
    supplierID bigint unsigned not null default 0,
    incrementQty int unsigned not null default 0,
    primary key (id),
    constraint PurchasingDataTemplate_fk_supplierID
    foreign key (supplierID) references Suppliers (SupplierID)
    on delete restrict
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `PurchasingCostTemplate` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `strategyID` bigint(20) unsigned NOT NULL DEFAULT '0',
  `minimumOrderQty` int(10) unsigned NOT NULL DEFAULT '0',
  `manufacturerLeadTime` smallint(5) unsigned NOT NULL DEFAULT '0',
  `supplierLeadTime` smallint(5) unsigned DEFAULT NULL,
  `binSize` int(10) unsigned NOT NULL DEFAULT '0',
  `unitCost` decimal(16,4) NOT NULL DEFAULT '0.0000',
  PRIMARY KEY (`id`),
  UNIQUE KEY `strategyID_minimumOrderQty_leadTime` (`strategyID`,`minimumOrderQty`,`manufacturerLeadTime`),
  CONSTRAINT `PurchasingCostTemplate_fk_strategyID`
  FOREIGN KEY (`strategyID`) REFERENCES `PurchasingDataTemplate` (`id`)
  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

alter table PurchasingCost
change column cost unitCost decimal(16,4) NOT NULL DEFAULT 0;

alter table PurchasingCost
drop column labourCost;

alter table ItemVersion
drop column volume,
add column dimensionX decimal(12,4) unsigned not null default 0,
add column dimensionY decimal(12,4) unsigned not null default 0,
add column dimensionZ decimal(12,4) unsigned not null default 0;