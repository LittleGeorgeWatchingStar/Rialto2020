alter table WorksOrders
add column dateUpdated datetime after dateCreated;

update WorksOrders
set dateUpdated = dateClosed where dateClosed is not null;

update WorksOrders wo
join (
    select WorkOrderID, max(IssueDate) as MaxIssueDate
    from WOIssues
    group by WorkOrderID
) i on wo.WORef = i.WorkOrderID
set wo.dateUpdated = i.MaxIssueDate
where wo.dateUpdated is null;

update WorksOrders
set dateUpdated = ReleasedDate
where dateUpdated is null and ReleasedDate is not null;

update WorksOrders
set dateUpdated = dateCreated
where dateUpdated is null and dateCreated is not null;