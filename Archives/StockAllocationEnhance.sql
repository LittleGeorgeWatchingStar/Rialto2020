-- Fix SalesOrderDetail
-- ALTER TABLE SalesOrderDetails ADD temp_orderno int(11);
-- ALTER TABLE SalesOrderDetails ADD temp_stkcode varchar(20);
-- UPDATE SalesOrderDetails SET temp_orderno = OrderNo;
-- UPDATE SalesOrderDetails SET temp_stkcode = StkCode;

ALTER TABLE SalesOrderDetails DROP PRIMARY KEY;
ALTER TABLE SalesOrderDetails
    ADD ID int unsigned not null AUTO_INCREMENT FIRST,
    ADD PRIMARY KEY (ID);


-- Axing Stock Request Table
--
-- Add columns to the table
ALTER TABLE StockAllocation 
    ADD ConsumerType ENUM('SalesOrderDetail','WorkOrderRequirement') not null
    AFTER AllocationID;
ALTER TABLE StockAllocation 
    ADD ConsumerNo int unsigned not null
    AFTER ConsumerType;

-- Modify StockAllocation to hold StockRequest Information, this requires modificaitons 
update StockAllocation as sa
join StockRequest as sr
on sa.RequestID = sr.RequestID
set sa.ConsumerType = 'SalesOrderDetail'
where sr.Type = 210;

update StockAllocation as sa
join StockRequest as sr
on sa.RequestID = sr.RequestID
set sa.ConsumerType = 'WorkOrderRequirement'
where sr.Type = 228;

-- Update IDs
update StockAllocation as sa
join StockRequest as sr
on sa.RequestID = sr.RequestID
join SalesOrderDetails as sod
on sr.TypeNo = sod.OrderNo
and sr.StockID = sod.StkCode
set sa.ConsumerNo = sod.ID
where sa.ConsumerType = 'SalesOrderDetail';

update StockAllocation as sa
join StockRequest as sr
on sa.RequestID = sr.RequestID
join WORequirements as wor
on sr.TypeNo = wor.WorkOrderID
and sr.StockID = wor.StockID
set sa.ConsumerNo = wor.ID
where sa.ConsumerType = 'WorkOrderRequirement';

--
-- Removing poorly organized Stock sources
--
ALTER TABLE StockAllocation 
    ADD SourceType ENUM('StockLevel','StockBin','PurchaseOrderDetail','WorkOrder')
    not null
    AFTER ConsumerNo;
ALTER TABLE StockAllocation 
    ADD SourceNo int unsigned not null
    AFTER SourceType;

-- Modify StockAllocation to grab StockSource Information
UPDATE StockAllocation SET SourceType='StockBin' WHERE Type='216';
UPDATE StockAllocation SET SourceType='StockLevel' WHERE Type='216' AND SerialNo='';
UPDATE StockAllocation SET SourceType='StockLevel' WHERE Type='216' AND SerialNo='0';
UPDATE StockAllocation SET SourceType='WorkOrder' WHERE Type='226';
UPDATE StockAllocation SET SourceType='PurchaseOrderDetail' WHERE Type='225';

-- Populate SourceType Field
UPDATE StockAllocation SET SourceNo=LocCode WHERE SourceType='StockLevel';
UPDATE StockAllocation SET SourceNo=SerialNo WHERE SourceType='StockBin';
UPDATE StockAllocation SET SourceNo=TypeNo WHERE SourceType='WorkOrder';
UPDATE StockAllocation SET SourceNo=TypeNo WHERE SourceType='PurchaseOrderDetail';


-- Update indexes to StockAllocation
alter table StockAllocation drop foreign key StockAllocation_ibfk_1;
alter table StockAllocation drop key RequestID;

alter table StockAllocation
add key (ConsumerType, ConsumerNo),
add key (SourceType, SourceNo),
add key (StockID);

-- Clean up invalid records
delete from StockAllocation where ConsumerType = '';
delete from StockAllocation where ConsumerType is null;
delete from StockAllocation where ConsumerNo = 0;
delete from StockAllocation where SourceType = '';
delete from StockAllocation where SourceType is null;
delete from StockAllocation where SourceNo = 0;

