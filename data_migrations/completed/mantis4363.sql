create or replace view TransferWorkOrder as
  select distinct transfer.id as transferID,
                  wo.id as workOrderID
  from LocTransferHeader transfer
    join LocTransfers transferItem on transferItem.Reference = transfer.ID
    join StockSerialItems bin on bin.SerialNo = transferItem.SerialNo
    join StockAllocation alloc on alloc.SourceNo = bin.SerialNo and alloc.SourceType = 'StockBin'
    join Requirement req on req.id = alloc.requirementID and req.consumerType = 'WorkOrder'
    join StockProducer wo on wo.id = req.consumerID
  where transfer.DateReceived is null
  and alloc.Qty > alloc.Delivered;


-- close old transfers
update LocTransferHeader transfer
  left join LocTransfers transferItem on transferItem.Reference = transfer.ID
set transfer.DateReceived = now()
where transferItem.ID is null
  and transfer.DateReceived is null;

update LocTransferHeader transfer
  join LocTransfers transferItem on transferItem.Reference = transfer.ID
  join StockSerialItems bin on bin.SerialNo = transferItem.SerialNo
set transfer.DateReceived = now(), transferItem.RecDate = now()
  where bin.LocCode != transfer.inTransitID
and transfer.DateReceived is null;

update LocTransferHeader transfer
  join LocTransfers transferItem on transferItem.Reference = transfer.ID
set transfer.DateReceived = now(), transferItem.RecDate = now()
where transferItem.SerialNo is null
  and transfer.DateReceived is null;

-- get rid of old work order <=> transfer mapping
rename table LocTransfersDetail to erp_archive.LocTransfersDetail;

