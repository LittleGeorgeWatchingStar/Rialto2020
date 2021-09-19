alter table PurchData
add column StockLevel int unsigned null default null;

alter table PurchasingCost
change column leadTime manufacturerLeadTime smallint unsigned not null default 0;

alter table PurchasingCost
add column supplierLeadTime smallint unsigned null default null after manufacturerLeadTime;

update PurchasingCost
set supplierLeadTime = 0;