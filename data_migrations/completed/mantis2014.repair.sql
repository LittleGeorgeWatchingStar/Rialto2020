-- EASY REPAIRS

alter table WorksOrders drop key WORef;
alter table WorksOrders drop key WORef_2;
alter table WorksOrders drop key RequiredBy;

alter table WORequirements
modify column WorkOrderID bigint unsigned not null default 0;

alter table WOIssues
modify column WorkOrderID bigint unsigned not null default 0;

alter table WOIssues
modify column IssueNo serial;

alter table WOIssueItems
modify column ID serial;

alter table WOIssueItems
modify column IssueID bigint unsigned not null default 0;

alter table WOIssueItems
add constraint WOIssueItems_fk_IssueID
foreign key (IssueID) references WOIssues (IssueNo)
on delete cascade;

update WOIssueItems set StockID = 'R15K' where StockID = 'R1501';
update WOIssueItems set StockID = 'R20K' where StockID = 'R203';
update WOIssueItems set StockID = 'CON90502R' where StockID = 'CON905';
update WOIssueItems set StockID = 'CON91603' where StockID = 'CON91600';
update WOIssueItems set StockID = 'ICP073' where StockID = 'ICP270';
update WOIssueItems set StockID = 'CC681A' where StockID = 'C681A';
update WOIssueItems set StockID = 'R1K650C' where StockID = 'R1K65C';
update WOIssueItems set StockID = 'R100KC' where StockID = 'R200KC';
update WOIssueItems set StockID = 'ICM025-400' where StockID = 'ICM025-416';
update WOIssueItems set StockID = 'ICM026-400' where StockID = 'ICM026-416';
update WOIssueItems set StockID = 'ICM026-600' where StockID = 'ICM026-624';

alter table WOIssueItems
add constraint WOIssueItems_fk_StockID
foreign key (StockID) references StockMaster (StockID)
on delete restrict on update cascade;

delete from WORequirements where WorkOrderID = 22305 and StockID = 'BRD000024';
delete from WORequirements where WorkOrderID = 22787 and StockID = 'GS200K-XMBT';
delete from WORequirements where WorkOrderID = 22871 and StockID = 'GS200K-XMBT';
delete from WORequirements where WorkOrderID = 26864 and StockID = 'R200KC';

update WORequirements set StockID = 'BRD00024' where StockID = 'BRD000024';
update WORequirements set StockID = 'CL104' where StockID = 'CL01';
update WORequirements set StockID = 'CC681A' where StockID = 'C681A';
update WORequirements set StockID = 'GS400K-XMBT' where StockID = 'GS200K-XMBT';
update WORequirements set StockID = 'ICI030' where StockID = 'ICI031';
update WORequirements set StockID = 'ICL140' where StockID = 'ICL140-US';
update WORequirements set StockID = 'ICM025-400' where StockID = 'ICM025-416';
update WORequirements set StockID = 'ICM026-400' where StockID = 'ICM026-416';
update WORequirements set StockID = 'ICM026-600' where StockID = 'ICM026-624';
update WORequirements set StockID = 'ICP073' where StockID = 'ICP270';
update WORequirements set StockID = 'RP140' where StockID = 'P140';
update WORequirements set StockID = 'R1K650C' where StockID = 'R1K65C';
update WORequirements set StockID = 'R100KC' where StockID = 'R200KC';
update WORequirements set StockID = 'R20K' where StockID = 'R203';
update WORequirements set StockID = 'R510R' where StockID = 'R501R0';
update WORequirements set StockID = 'R510R' where StockID = 'R510R0';

alter table WORequirements
add constraint WORequirements_fk_StockID
foreign key (StockID) references StockMaster (StockID)
on delete restrict on update cascade;


-- THESE APPEAR DESTRUCTIVE BUT ARE REALLY OKAY

delete from WOIssues
where WorkOrderID not in (select WORef from WorksOrders);

delete from WOIssues where IssueNo = 1314;

alter table WOIssues
add constraint WOIssues_fk_WorkOrderID
foreign key (WorkOrderID) references WorksOrders (WORef)
on delete restrict;


-- NOW ADD THE NEW COLUMNS NEEDED
alter table WOIssues
add column qtyIssued decimal(12,4) unsigned not null default 0,
add column qtyReceived decimal(12,4) unsigned not null default 0;

alter table WOIssueItems
add column unitQtyIssued decimal(12,4) unsigned not null default 0,
add column scrapIssued decimal(12,4) unsigned not null default 0;