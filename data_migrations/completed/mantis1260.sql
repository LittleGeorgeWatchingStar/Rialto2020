alter table WORequirements
add column ScrapCount int unsigned not null default 0;

alter table WOIssueItems
add column UnitStandardCost decimal(10,4) not null default 0.0;