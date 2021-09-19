drop table if exists StockProducer;
create table StockProducer (
    id serial,
    workOrderID bigint unsigned null,
    poItemID bigint unsigned null,
    `type` varchar(10) not null default 'parts',
    purchaseOrderID bigint unsigned null,
    locationID varchar(5) null,
    parentID bigint unsigned null,

    purchasingDataID bigint unsigned null,

    stockCode varchar(20) null,
    version varchar(31) not null default '',
    customizationID bigint unsigned null,

    description varchar(100) not null default '',
    instructions text,

    dateCreated datetime not null,
    dateReleased datetime null,
    dateUpdated datetime null,
    dateClosed datetime null,
    requestedDate date null,
    commitmentDate date null,

    qtyOrdered decimal(16,4) not null,
    qtyIssued decimal(16,4) not null default 0,
    qtyReceived decimal(16,4) not null default 0,
    qtyInvoiced decimal(16,4) not null default 0,

    expectedUnitCost decimal(16,4) not null,
    actualUnitCost decimal(16,4) not null default 0,
    glAccountID int unsigned null,

    openForAllocation boolean not null default 1,
    rework boolean not null default 0,
    flags varchar(255) not null default '',

    primary key (id),
    unique key (workOrderID),
    unique key (poItemID)
) engine=InnoDB default character set=utf8;

insert into StockProducer
(id, workOrderID, poItemID, `type`, purchaseOrderID, locationID, parentID,
purchasingDataID,
stockCode, version, customizationID,
description,
glAccountID,
instructions,
dateCreated, dateReleased, dateUpdated, dateClosed, requestedDate, commitmentDate,
qtyOrdered, qtyIssued, qtyReceived, qtyInvoiced,
expectedUnitCost, actualUnitCost,
openForAllocation, rework, flags)
select
wo.WORef, wo.WORef, pi.PODetailItem, 'labour', wo.OrderNo, wo.LocCode, wo.ParentBuild,
wo.PurchDataID,
wo.StockID, wo.Version, wo.CustomizationID,
if(pi.ItemDescription != '', pi.ItemDescription, concat('Labour: ', wo.StockID)),
if(pi.GLCode, pi.GLCode, cat.StockAct),
wo.Instructions,
wo.dateCreated, wo.ReleasedDate, wo.dateUpdated, wo.dateClosed, pi.RequestedDate, pi.CommitmentDate,
wo.UnitsReqd, wo.UnitsIssued, wo.UnitsRecd, ifnull(pi.QtyInvoiced, 0),
ifnull(pi.UnitPrice, 0), ifnull(pi.ActPrice, 0),
wo.OpenForAllocation, wo.Rework, ifnull(pi.Flags, if(pi.UnitPrice = 0, 'zero_cost', ''))
from
WorksOrders wo
left join PurchOrderDetails pi
    on wo.OrderNo = pi.OrderNo
    and pi.ItemDescription like concat('Labour: ', wo.StockID, '%')
    and pi.PODetailItem not in (5072)
left join StockMaster item
    on wo.StockID = item.StockID
left join StockCategory cat
     on item.CategoryID = cat.CategoryID;

insert into StockProducer
(poItemID, `type`, purchaseOrderID,
purchasingDataID,
stockCode, version,
description, glAccountID, instructions,
dateCreated, dateReleased, dateUpdated, dateClosed, requestedDate, commitmentDate,
qtyOrdered, qtyIssued, qtyReceived, qtyInvoiced,
expectedUnitCost, actualUnitCost,
openForAllocation, rework, flags)
select
pi.PODetailItem, 'parts', pi.OrderNo,
pi.PurchDataID,
pi.ItemCode, pi.VersionReference,
pi.ItemDescription, pi.GLCode, pi.VersionReference,
po.OrdDate, null, null, pi.DeliveryDate, pi.RequestedDate, pi.CommitmentDate,
pi.QuantityOrd, 0, pi.QuantityRecd, pi.QtyInvoiced,
pi.UnitPrice, pi.ActPrice,
1, 0, pi.Flags
from PurchOrderDetails pi
join PurchOrders po
    on pi.OrderNo = po.OrderNo
where pi.ItemDescription not like 'Labour:%';

update StockAllocation a
join StockProducer p
    on a.SourceNo = p.workOrderID
set a.SourceType = 'StockProducer',
a.SourceNo = p.id
where SourceType = 'WorkOrder';

update StockAllocation a
join StockProducer p
    on a.SourceNo = p.poItemID
set a.SourceType = 'StockProducer',
a.SourceNo = p.id
where SourceType = 'PurchaseOrderDetail';

delete from StockAllocation where SourceType in ('PurchaseOrderDetail', 'StockLevel');
alter table StockAllocation drop column ConsumerType;
alter table StockAllocation drop column ConsumerNo;

alter table WOIssues drop foreign key WOIssues_fk_WorkOrderID;
alter table WOIssues add constraint WOIssues_fk_WorkOrderID
foreign key (WorkOrderID) references StockProducer (id)
on delete restrict;

alter table SalesReturnItem
drop foreign key `SalesReturnItem_fk_originalWorkOrder`,
drop foreign key `SalesReturnItem_fk_reworkOrder`;
alter table SalesReturnItem
add CONSTRAINT `SalesReturnItem_fk_originalWorkOrder` FOREIGN KEY (`originalWorkOrder`) REFERENCES `StockProducer` (`id`),
add CONSTRAINT `SalesReturnItem_fk_reworkOrder` FOREIGN KEY (`reworkOrder`) REFERENCES `StockProducer` (`id`) ON DELETE SET NULL;

drop table if exists GoodsReceivedItem;
create table GoodsReceivedItem (
    id serial,
    oldID bigint unsigned null,
    grnID bigint unsigned null,
    producerID bigint unsigned null,
    stockCode varchar(20) null,
    receivedInto varchar(5) null,
    dateReceived datetime not null,
    qtyReceived decimal(16,4) not null,
    qtyInvoiced decimal(16,4) not null default 0,
    invoiceItemID bigint unsigned null,
    standardUnitCost decimal(16,4) not null default 0,
    discarded boolean default 0,
    binStyle varchar(20) null,
    primary key (id),
    constraint GoodsReceivedItem_fk_grnID
    foreign key (grnID) references GoodsReceivedNotice (BatchID)
    on delete cascade,
    constraint GoodsReceivedItem_fk_producerID
    foreign key (producerID) references StockProducer (id)
    on update cascade on delete restrict,
    constraint GoodsReceivedItem_fk_receivedInto
    foreign key (receivedInto) references Locations (LocCode)
    on update cascade on delete restrict,
    constraint GoodsReceivedItem_fk_binStyle
    foreign key (binStyle) references BinStyle (name)
    on update cascade on delete restrict
);

insert into GoodsReceivedItem
(oldID, grnID, producerID,
stockCode, receivedInto, dateReceived,
qtyReceived,
qtyInvoiced,
invoiceItemID,
standardUnitCost)
select
grn.GRNNo, grn.GRNBatch, sp.id,
ifnull(grn.ItemCode, sp.stockCode), po.IntoStockLocation, grn.DeliveryDate,
ifnull(move.quantity, grn.QtyRecd),
if(move.quantity is null, grn.QuantityInv, least(move.quantity, grn.QuantityInv)),
ifnull(bin.invoiceItemID, grn.invoiceItemID),
ifnull(move.unitStandardCost, ifnull(pod.StdCostUnit, 0))
from GRNs grn
left join StockProducer sp
    on grn.PODetailItem = sp.poItemID
left join PurchOrderDetails pod
    on grn.PODetailItem = pod.PODetailItem
left join PurchOrders po
    on pod.OrderNo = po.OrderNo
left join StockMove move
    on move.systemTypeID = 25
    and move.systemTypeNumber = grn.GRNBatch
    and move.stockCode = grn.ItemCode
left join StockSerialItems bin
    on move.binID = bin.SerialNo;
