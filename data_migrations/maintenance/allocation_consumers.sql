select * from StockAllocation
where ConsumerType = 'WorkOrderRequirement'
and ConsumerNo not in ( select ID from WORequirements );

select a.* from StockAllocation a
join StockMaster i on a.StockID = i.StockID
where a.SourceType = 'StockLevel'
and i.Controlled = 1;

-- find and delete allocations with bad consumer references
select a.* from StockAllocation a
left join SalesOrderDetails d
    on a.ConsumerNo = d.ID
where a.ConsumerType = 'SalesOrderDetail'
and d.ID is null;

select * from StockAllocation
where ConsumerType = 'SalesOrderDetail'
and ConsumerNo not in (select ID from SalesOrderDetails);

delete a
from StockAllocation a
left join SalesOrderDetails d
    on a.ConsumerNo = d.ID
where a.ConsumerType = 'SalesOrderDetail'
and d.ID is null;

select * from StockAllocation
where ConsumerType = 'WorkOrderRequirement'
and ConsumerNo not in (select ID from WORequirements);
